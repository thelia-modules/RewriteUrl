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
use RewriteUrl\Model\Map\RewriteurlErrorUrlTableMap;
use RewriteUrl\Model\RewriteurlErrorUrlQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Generic;


class RewriteUrlErrorUrlLoop extends Generic
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        $argumentCollection = parent::getArgDefinitions();
        $argumentCollection->addArgument(Argument::createAnyTypeArgument('search'));
        return $argumentCollection;
    }

    /**
     * @return ModelCriteria|RewriteurlRuleQuery
     */
    public function buildModelCriteria()
    {
        /** @var RewriteurlErrorUrlQuery $query */
        $query = parent::buildModelCriteria();

        if (null !== $searchTerm = $this->getSearch()) {
            $query
                ->where(RewriteurlErrorUrlTableMap::COL_URL_SOURCE .' LIKE "%'.$searchTerm.'%"')
                ->_or()
                ->where(RewriteurlErrorUrlTableMap::COL_USER_AGENT .' LIKE "%'.$searchTerm.'%"');
        }

        return $query;
    }
}