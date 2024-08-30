<?php

namespace RewriteUrl\Loop;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlErrorUrl;
use RewriteUrl\Model\RewriteurlErrorUrlQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * @method getId()
 * @method getUserAgent()
 * @method getUrlSource()
 * @method getOrders()
 */
class RewriteUrlErrorUrlLoop extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @throws PropelException
     */
    public function parseResults(LoopResult $loopResult): LoopResult
    {
        /** @var RewriteurlErrorUrl $rewriteUrlErrorUrl */
        foreach ($loopResult->getResultDataCollection() as $rewriteUrlErrorUrl) {
            $loopResultRow = new LoopResultRow($rewriteUrlErrorUrl);

            $loopResultRow->set('ID', $rewriteUrlErrorUrl->getId())
                ->set('URL_SOURCE', $rewriteUrlErrorUrl->getUrlSource())
                ->set('COUNT', $rewriteUrlErrorUrl->getCount())
                ->set('USER_AGENT', $rewriteUrlErrorUrl->getUserAgent())
                ->set('REWRITEURL_RULE_ID', $rewriteUrlErrorUrl->getRewriteurlRuleId())
                ->set('REDIRECT_URL', $rewriteUrlErrorUrl->getRewriteurlRule()?->getRedirectUrl())
                ->set('CREATED_AT', $rewriteUrlErrorUrl->getCreatedAt())
                ->set('UPDATED_AT', $rewriteUrlErrorUrl->getUpdatedAt());
            $this->addOutputFields($loopResultRow, $rewriteUrlErrorUrl);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

    public function buildModelCriteria(): ModelCriteria
    {
        $search = RewriteUrlErrorUrlQuery::create();

        if (null !== $id = $this->getId()) {
            $search->filterById($id);
        }

        if (null !== $userAgent = $this->getUserAgent()) {
            $search->filterByUserAgent($userAgent);
        }

        if (null !== $userSource = $this->getUrlSource()) {
            $search->filterByUrlSource($userSource);
        }

        if (null !== $orders = $this->getOrders()) {
            [$order, $criteria] = explode(":", $orders);
            $search->orderBy($order, $criteria);
        }

        return $search;
    }

    protected function getArgDefinitions(): ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('id'),
            Argument::createAnyTypeArgument('user_agent'),
            Argument::createAnyTypeArgument('url_source'),
            Argument::createAnyTypeArgument('orders'),
        );
    }
}