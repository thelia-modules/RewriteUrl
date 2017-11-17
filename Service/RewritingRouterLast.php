<?php

namespace RewriteUrl\Service;

use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Thelia\Core\Routing\RewritingRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Thelia\Core\HttpFoundation\Request as TheliaRequest;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;

/**
 * This router is intended to be the very last checked by the ChainRouter on a request.
 */
class RewritingRouterLast extends RewritingRouter
{
    /**
     * @inheritdoc
     */
    public function matchRequest(Request $request)
    {
        if (ConfigQuery::isRewritingEnable()) {
            $urlTool = URL::getInstance();

            $ruleCollection = RewriteurlRuleQuery::create()
                ->filterByOnly404(1)
                ->orderByPosition()
                ->find();

            $pathInfo = $request instanceof TheliaRequest ? $request->getRealPathInfo() : $request->getPathInfo();

            /** @var RewriteurlRule $rule */
            foreach ($ruleCollection as $rule) {
                if ($rule->isMatching($pathInfo, $request->query->all())) {
                    $this->redirect($urlTool->absoluteUrl($rule->getRedirectUrl()), 301);
                }
            }
        }
        throw new ResourceNotFoundException();
    }
}
