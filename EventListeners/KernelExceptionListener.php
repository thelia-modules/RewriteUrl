<?php

namespace RewriteUrl\EventListeners;

use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlErrorUrl;
use RewriteUrl\Model\RewriteurlErrorUrlQuery;
use RewriteUrl\Model\RewriteurlErrorUrlRefererQuery;
use RewriteUrl\Model\RewriteurlErrorUrlReferrerQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Core\HttpKernel\Exception\RedirectException;
use Thelia\Core\HttpFoundation\Request as TheliaRequest;
use Thelia\Tools\URL;

class KernelExceptionListener implements EventSubscriberInterface
{
    /** @var RequestStack */

    public function __construct(protected RequestStack $requestStack)
    { }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelHttpNotFoundException', 300],
        ];
    }

    /**
     * @throws PropelException
     */
    public function onKernelHttpNotFoundException(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $urlTool = URL::getInstance();

            $request = $this->requestStack->getCurrentRequest();
            $pathInfo = $request instanceof TheliaRequest ? $request->getRealPathInfo() : $request->getPathInfo();

            // Errors Url
            $userAgent = $this->requestStack->getCurrentRequest()->headers->get('user_agent');

            if (null === $errorUrl = RewriteurlErrorUrlQuery::create()->findOneByUrlSource($pathInfo)) {
                $rewriteUrlRule = RewriteurlRuleQuery::create()
                    ->filterByRuleType(RewriteurlRule::TYPE_TEXT)
                    ->filterByOnly404(1)
                    ->findOneByValue($pathInfo);

                if (null === $rewriteUrlRule) {
                    $errorUrl = new RewriteurlErrorUrl();
                    $errorUrl
                        ->setUrlSource($pathInfo)
                        ->setCount(0)
                    ;
                }
            }

            if ((null !== $errorUrl) && null === RewriteurlRuleQuery::create()->findOneById($errorUrl->getRewriteurlRuleId())) {
                $errorUrl
                    ->setUserAgent($userAgent)
                    ->setCount($errorUrl->getCount() + 1)
                    ->save()
                ;

                if (null !== $request->server->get('HTTP_REFERER')){
                    $errorUrlReferer = RewriteurlErrorUrlRefererQuery::create()
                        ->filterByRewriteurlErrorUrlId($errorUrl->getId())
                        ->filterByReferer($request->server->get('HTTP_REFERER'))
                        ->findOneOrCreate();

                    $errorUrlReferer->save();
                }
            }
            // End Errors Url

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
    }
}
