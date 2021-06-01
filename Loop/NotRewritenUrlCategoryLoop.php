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

use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseI18nLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Base\BrandQuery;
use Thelia\Model\Brand;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\Content;
use Thelia\Model\ContentQuery;
use Thelia\Model\Folder;
use Thelia\Model\FolderQuery;
use Thelia\Model\LangQuery;
use Thelia\Model\Map\RewritingUrlTableMap;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\RewritingUrlQuery;

/**
 * Class NotRewritenUrlCategoryLoop
 * @package RewriteUrl\Loop
 * @author Tom Pradat <tpradat@openstudio.fr>
 * @author Gilles Bourgeat <gilles@thelia.net>
 *
 * @method string getView()
 */
class NotRewritenUrlCategoryLoop extends BaseI18nLoop implements PropelSearchLoopInterface
{
    protected static $cacheRewritingUrl = [];

    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createEnumListTypeArgument(
                'view',
                ['product', 'category', 'folder', 'content', 'brand']
            )
        );
    }

    public function buildModelCriteria()
    {
        $view = $this->getView()[0];

        $rewritingUrlQuery = RewritingUrlQuery::create();

        $class = 'Thelia\Model\\' . ucfirst($view) . 'Query';
        /** @var CategoryQuery|ProductQuery|FolderQuery|ContentQuery|BrandQuery $objectQuery */
        $objectQuery = $class::create();

        $localeSearch = LangQuery::create()->findByIdOrLocale($this->getLang());

        $rewritingUrlQuery->filterByView($view)->filterByViewLocale(
            $localeSearch !== null ? $localeSearch->getLocale() : 'en_US'
        );

        if (!isset(static::$cacheRewritingUrl[$view])) {
            static::$cacheRewritingUrl[$view] = $rewritingUrlQuery
                ->select([RewritingUrlTableMap::VIEW_ID])
                ->groupBy(RewritingUrlTableMap::VIEW_ID)
                ->find();
        }

        $query = $objectQuery
            ->filterById(static::$cacheRewritingUrl[$view]->getData(), Criteria::NOT_IN);

        /* manage translations */
        $this->configureI18nProcessing($query, ['TITLE']);

        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var Category|Product|Folder|Content|Brand $category */
        foreach ($loopResult->getResultDataCollection() as $category) {
            $loopResultRow = (new LoopResultRow($category))
                ->set('ID', $category->getId())
                ->set('NAME', $category->getVirtualColumn('i18n_TITLE'));

            if (property_exists($category, 'ref')) {
                $loopResultRow->set('REF', $category->getRef());
            }
            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
