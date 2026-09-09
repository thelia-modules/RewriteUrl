<?php

namespace RewriteUrl\EventListeners;

use Propel\Runtime\Exception\PropelException;
use Psr\Log\LoggerInterface;
use RewriteUrl\Model\RewriteurlErrorUrl;
use RewriteUrl\Model\RewriteurlErrorUrlQuery;
use RewriteUrl\Model\RewriteurlErrorUrlReferer;
use RewriteUrl\Model\RewriteurlErrorUrlRefererQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Core\HttpKernel\Exception\RedirectException;
use Thelia\Core\HttpFoundation\Request as TheliaRequest;
use Thelia\Tools\URL;

class KernelExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        protected RequestStack $requestStack,
        protected LoggerInterface $logger,
    )
    { }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelHttpNotFoundException', 300],
        ];
    }

    public function onKernelHttpNotFoundException(ExceptionEvent $event): void
    {
        if (!$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $urlTool = URL::getInstance();

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $pathInfo = $request instanceof TheliaRequest ? $request->getRealPathInfo() : $request->getPathInfo();

        $this->recordErrorUrl($request, $pathInfo);

        // Check RewriteUrl text rules
        $textRule = RewriteurlRuleQuery::create()
            ->filterByOnly404(0)
            ->filterByValue(ltrim($pathInfo, '/'))
            ->filterByRuleType('text')
            ->orderByPosition()
            ->findOne();

        if ($textRule) {
            $event->setThrowable(new RedirectException($urlTool?->absoluteUrl($textRule->getRedirectUrl()), 301));
        }

        $ruleCollection = RewriteurlRuleQuery::create()
            ->filterByOnly404(1)
            ->orderByPosition()
            ->find();

        /** @var RewriteurlRule $rule */
        foreach ($ruleCollection as $rule) {
            if ($rule->isMatching($pathInfo, $request->query->all())) {
                $event->setThrowable(new RedirectException($urlTool?->absoluteUrl($rule->getRedirectUrl()), 301));
                return;
            }
        }
    }

    /**
     * Records the 404 in the back-office error url report.
     *
     * The path, the user agent and the referer come from the request and have no length
     * limit of their own, so they are cut to the width of their column. A write that
     * fails all the same is logged and swallowed: this report is a side effect of the
     * 404, and must never turn it into a 500.
     */
    private function recordErrorUrl(Request $request, string $pathInfo): void
    {
        try {
            $userAgent = $request->headers->get('user_agent');

            if (null === $errorUrl = RewriteurlErrorUrlQuery::create()->findOneByUrlSource($pathInfo)) {
                $rewriteUrlRule = RewriteurlRuleQuery::create()
                    ->filterByRuleType(RewriteurlRule::TYPE_TEXT)
                    ->filterByOnly404(1)
                    ->findOneByValue($pathInfo);

                if (null === $rewriteUrlRule) {
                    $errorUrl = new RewriteurlErrorUrl();
                    $errorUrl
                        ->setUrlSource($this->cut($pathInfo, RewriteurlErrorUrl::URL_SOURCE_MAX_LENGTH))
                        ->setCount(0)
                    ;
                }
            }

            if (null === $errorUrl || null !== RewriteurlRuleQuery::create()->findOneById($errorUrl->getRewriteurlRuleId())) {
                return;
            }

            $errorUrl
                ->setUserAgent($this->cut($userAgent ?? 'N/A', RewriteurlErrorUrl::USER_AGENT_MAX_LENGTH))
                ->setCount($errorUrl->getCount() + 1)
                ->save()
            ;

            if (null === $referer = $request->server->get('HTTP_REFERER')) {
                return;
            }

            RewriteurlErrorUrlRefererQuery::create()
                ->filterByRewriteurlErrorUrlId($errorUrl->getId())
                ->filterByReferer($this->cut($referer, RewriteurlErrorUrlReferer::REFERER_MAX_LENGTH))
                ->findOneOrCreate()
                ->save()
            ;
        } catch (PropelException $exception) {
            $this->logger->error(
                'RewriteUrl: could not record the 404 on "{path}": {message}',
                ['path' => $pathInfo, 'message' => $exception->getMessage(), 'exception' => $exception]
            );
        }
    }

    /**
     * Cuts to a byte length on a character boundary: the column counts bytes when the
     * table is latin1, characters when it is utf8mb4, and mb_strcut never overflows
     * either nor splits a multibyte character in half.
     */
    private function cut(string $value, int $maxLength): string
    {
        return mb_strcut($value, 0, $maxLength);
    }
}
