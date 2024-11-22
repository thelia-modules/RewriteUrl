<?php
/*************************************************************************************/
/*      This file is part of the RewriteUrl module for Thelia.                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace RewriteUrl\Loop;


use Propel\Runtime\ActiveQuery\ModelCriteria;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;


class RewriteUrlRuleLoop extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('id'),
            Argument::createAnyTypeArgument('rule_type'),
            Argument::createAnyTypeArgument('value'),
            Argument::createAnyTypeArgument('redirect_url')

        );
    }

    /**
     * @return ModelCriteria|RewriteurlRuleQuery
     */
    public function buildModelCriteria()
    {
        $search = RewriteurlRuleQuery::create();

        if (null !== $id = $this->getId()){
            $search->filterById($id);
        }

        if (null !== $ruleType = $this->getRuleType()){
            $search->filterByRuleType($ruleType);
        }

        if (null !== $value = $this->getValue()){
            $search->filterByValue($value);
        }

        if (null !== $redirectUrl = $this->getRedirectUrl()){
            $search->filterByRedirectUrl($redirectUrl);
        }

        return $search->orderByPosition();
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var RewriteurlRule $rewriteUrlRule */
        foreach ($loopResult->getResultDataCollection() as $rewriteUrlRule){
            $loopResultRow = (new LoopResultRow($rewriteUrlRule))
                ->set('ID', $rewriteUrlRule->getId())
                ->set('RULE_TYPE', $rewriteUrlRule->getRuleType())
                ->set('VALUE', $rewriteUrlRule->getValue())
                ->set('ONLY404', $rewriteUrlRule->getOnly404())
                ->set('REDIRECT_URL', $rewriteUrlRule->getRedirectUrl())
                ->set('POSITION', $rewriteUrlRule->getPosition())
                ->set('REWRITE_URL_PARAMS', $rewriteUrlRule->getRewriteUrlParamCollection());

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}