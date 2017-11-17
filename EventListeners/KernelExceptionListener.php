<?php

namespace RewriteUrl\EventListeners;

use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Core\HttpKernel\Exception\RedirectException;
use Thelia\Core\HttpFoundation\Request as TheliaRequest;
use Thelia\Tools\URL;

class KernelExceptionListener implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }


    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelHttpNotFoundException', 300],
        ];
    }

    public function onKernelHttpNotFoundException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof NotFoundHttpException) {
            $urlTool = URL::getInstance();

            $ruleCollection = RewriteurlRuleQuery::create()
                ->filterByOnly404(1)
                ->orderByPosition()
                ->find();

            $request = $this->requestStack->getCurrentRequest();
            $pathInfo = $request instanceof TheliaRequest ? $request->getRealPathInfo() : $request->getPathInfo();

            /** @var RewriteurlRule $rule */
            foreach ($ruleCollection as $rule) {
                if ($rule->isMatching($pathInfo, $request->query->all())) {
                    $event->setException(new RedirectException($urlTool->absoluteUrl($rule->getRedirectUrl()), 301));
                    return;
                }
            }
        }
    }
}
