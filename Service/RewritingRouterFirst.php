<?php

namespace RewriteUrl\Service;

use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use RewriteUrl\Model\RewritingRedirectType;
use RewriteUrl\Model\RewritingRedirectTypeQuery;
use Thelia\Core\Routing\RewritingRouter;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Thelia\Core\HttpFoundation\Request as TheliaRequest;
use Thelia\Exception\UrlRewritingException;
use Thelia\Model\ConfigQuery;
use Thelia\Model\LangQuery;
use Thelia\Model\RewritingUrlQuery;
use Thelia\Tools\URL;

/**
 * This router is intended to be the very first checked by the ChainRouter on a request.
 */
class RewritingRouterFirst extends RewritingRouter
{
    /**
     * @inheritdoc
     */
    public function matchRequest(Request $request)
    {
        if (ConfigQuery::isRewritingEnable()) {
            $urlTool = URL::getInstance();

            $pathInfo = $request instanceof TheliaRequest ? $request->getRealPathInfo() : $request->getPathInfo();



            // Check RewriteUrl rules

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



            try {
                $rewrittenUrlData = $urlTool->resolve($pathInfo);
            } catch (UrlRewritingException $e) {
                switch ($e->getCode()) {
                    case UrlRewritingException::URL_NOT_FOUND:
                        throw new ResourceNotFoundException();
                        break;
                    default:
                        throw $e;
                }
            }

            // If we have a "lang" parameter, whe have to check if the found URL has the proper locale
            // If it's not the case, find the rewritten URL with the requested locale, and redirect to it.
            if (null ==! $requestedLocale = $request->get('lang')) {
                if (null !== $requestedLang = LangQuery::create()->findOneByLocale($requestedLocale)) {
                    if ($requestedLang->getLocale() != $rewrittenUrlData->locale) {
                        $localizedUrl = $urlTool->retrieve(
                            $rewrittenUrlData->view,
                            $rewrittenUrlData->viewId,
                            $requestedLang->getLocale()
                        )->toString();

                        $this->redirect($urlTool->absoluteUrl($localizedUrl), 301);
                    }
                }
            }

            /* is the URL redirected ? */
            if (null !== $rewrittenUrlData->redirectedToUrl) {
                $redirect = RewritingUrlQuery::create()
                    ->filterByView($rewrittenUrlData->view)
                    ->filterByViewId($rewrittenUrlData->viewId)
                    ->filterByViewLocale($rewrittenUrlData->locale)
                    ->filterByRedirected(null, Criteria::ISNULL)
                    ->findOne()
                ;

                // Differences with the base class for handling 301 or 302 redirection
                $redirectType = $this->fetchRewritingRedirectTypeFromUrl($rewrittenUrlData->rewrittenUrl);

                if ($redirectType == null) {
                    $httpRedirectCode = RewritingRedirectType::DEFAULT_REDIRECT_TYPE;
                } else {
                    $httpRedirectCode = $redirectType->getHttpcode();
                }
                // End of differences

                $this->redirect($urlTool->absoluteUrl($redirect->getUrl()), $httpRedirectCode);
            }

            /* define GET arguments in request */

            if (null !== $rewrittenUrlData->view) {
                $request->attributes->set('_view', $rewrittenUrlData->view);
                if (null !== $rewrittenUrlData->viewId) {
                    $request->query->set($rewrittenUrlData->view . '_id', $rewrittenUrlData->viewId);
                }
            }

            if (null !== $rewrittenUrlData->locale) {
                $this->manageLocale($rewrittenUrlData, $request);
            }


            foreach ($rewrittenUrlData->otherParameters as $parameter => $value) {
                $request->query->set($parameter, $value);
            }

            return array(
                '_controller' => 'Thelia\\Controller\\Front\\DefaultController::noAction',
                '_route' => 'rewrite',
                '_rewritten' => true,
            );
        }
        throw new ResourceNotFoundException();
    }

    /**
     * @param $url
     * @return RewritingRedirectType
     */
    public function fetchRewritingRedirectTypeFromUrl($url)
    {
        return RewritingRedirectTypeQuery::create()
            ->joinRewritingUrl()
            ->useRewritingUrlQuery()
            ->filterByUrl($url)
            ->endUse()
            ->findOne();
    }
}
