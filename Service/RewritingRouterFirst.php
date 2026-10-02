<?php

namespace RewriteUrl\Service;

use Propel\Runtime\ActiveQuery\Criteria;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use RewriteUrl\Model\RewritingRedirectType;
use RewriteUrl\Model\RewritingRedirectTypeQuery;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Request as TheliaRequest;
use Thelia\Core\Routing\RewritingRouter;
use Thelia\Core\Routing\Rewriting\RewritingResolver;
use Thelia\Model\ConfigQuery;
use Thelia\Model\RewritingUrlQuery;
use Thelia\Tools\URL;

/**
 * The very first router checked by the ChainRouter on a request: above the rewriting router of the core
 * (`router.rewrite`, priority 1024 in RegisterRouterPass), so that the rules which do not wait for a 404 and
 * the redirect type of a redirected url (301 or 302) apply before the core answers. Everything else is the
 * rewriting of the core.
 */
#[AutoconfigureTag('router.register', ['priority' => 1100])]
class RewritingRouterFirst extends RewritingRouter
{
    /**
     * @inheritdoc
     */
    public function matchRequest(Request $request): array
    {
        if (ConfigQuery::isRewritingEnable()) {
            $this->applyRules($request);
        }

        return parent::matchRequest($request);
    }

    /**
     * The core redirects a replaced url with a 301; the module keeps the code chosen for that url.
     */
    protected function maybeRedirectForManualRedirect(RewritingResolver $resolver): void
    {
        if (null === $resolver->redirectedToUrl) {
            return;
        }

        $redirect = RewritingUrlQuery::create()
            ->filterByView($resolver->view)
            ->filterByViewId($resolver->viewId)
            ->filterByViewLocale($resolver->locale)
            ->filterByRedirected(null, Criteria::ISNULL)
            ->findOne();

        $this->redirect(
            URL::getInstance()->absoluteUrl($redirect?->getUrl() ?? $resolver->redirectedToUrl),
            $this->fetchRewritingRedirectTypeFromUrl($resolver->rewrittenUrl)?->getHttpcode() ?? RewritingRedirectType::DEFAULT_REDIRECT_TYPE
        );
    }

    /**
     * @param string|null $url
     * @return RewritingRedirectType|null
     */
    public function fetchRewritingRedirectTypeFromUrl($url)
    {
        if (null === $url) {
            return null;
        }

        return RewritingRedirectTypeQuery::create()
            ->joinRewritingUrl()
            ->useRewritingUrlQuery()
            ->filterByUrl($url)
            ->endUse()
            ->findOne();
    }

    private function applyRules(Request $request): void
    {
        $urlTool = URL::getInstance();
        $pathInfo = $request instanceof TheliaRequest ? $request->getRealPathInfo() : $request->getPathInfo();

        $textRule = RewriteurlRuleQuery::create()
            ->filterByOnly404(0)
            ->filterByValue(ltrim($pathInfo, '/'))
            ->filterByRuleType('text')
            ->orderByPosition()
            ->findOne();

        if ($textRule) {
            $this->redirect($urlTool->absoluteUrl($textRule->getRedirectUrl()), 301);
        }

        $ruleCollection = RewriteurlRuleQuery::create()
            ->filterByOnly404(0)
            ->orderByPosition()
            ->find();

        /** @var RewriteurlRule $rule */
        foreach ($ruleCollection as $rule) {
            if ($rule->isMatching($pathInfo, $request->query->all())) {
                $this->redirect($urlTool->absoluteUrl($rule->getRedirectUrl()), 301);
            }
        }
    }
}
