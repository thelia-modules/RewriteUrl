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
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Template\Element\BaseI18nLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Base\BrandQuery;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ContentQuery;
use Thelia\Model\FolderQuery;
use Thelia\Model\LangQuery;
use Thelia\Model\Map\BrandTableMap;
use Thelia\Model\Map\CategoryTableMap;
use Thelia\Model\Map\ContentTableMap;
use Thelia\Model\Map\FolderTableMap;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Model\Map\RewritingUrlTableMap;
use Thelia\Model\ProductQuery;

/**
 * Class NotRewritenUrlCategoryLoop
 * @package RewriteUrl\Loop
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class NotRewritenUrlCategoryLoop extends BaseI18nLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createAnyTypeArgument('categorie')
        );
    }

    public function buildModelCriteria()
    {
        $localequery = LangQuery::create()->findOneById($this->getLang());
        $locale = $localequery->getLocale();
        $cat = ucfirst($this->getCategorie());
        $objectQuery = null;
        $urlJoin = null;

        switch ($cat) {
            case 'Product':
                $objectQuery = ProductQuery::create();
                $urlJoin = new Join(ProductTableMap::ID, RewritingUrlTableMap::VIEW_ID, Criteria::LEFT_JOIN);
                break;
            case 'Brand':
                $objectQuery = BrandQuery::create();
                $urlJoin = new Join(BrandTableMap::ID, RewritingUrlTableMap::VIEW_ID, Criteria::LEFT_JOIN);
                break;
            case 'Category':
                $objectQuery = CategoryQuery::create();
                $urlJoin = new Join(CategoryTableMap::ID, RewritingUrlTableMap::VIEW_ID, Criteria::LEFT_JOIN);
                break;
            case 'Folder':
                $objectQuery = FolderQuery::create();
                $urlJoin = new Join(FolderTableMap::ID, RewritingUrlTableMap::VIEW_ID, Criteria::LEFT_JOIN);
                break;
            case 'Content':
                $objectQuery = ContentQuery::create();
                $urlJoin = new Join(ContentTableMap::ID, RewritingUrlTableMap::VIEW_ID, Criteria::LEFT_JOIN);
                break;
        }

        $query = $objectQuery
            ->joinWithI18n($locale)
            ->addJoinObject($urlJoin, 'url_rewriting_join')
            ->addJoinCondition(
                'url_rewriting_join',
                RewritingUrlTableMap::VIEW . ' = ?',
                strtolower($cat),
                null,
                \PDO::PARAM_STR
            )
            ->addJoinCondition(
                'url_rewriting_join',
                RewritingUrlTableMap::VIEW_LOCALE . ' = ?',
                $locale,
                null,
                \PDO::PARAM_STR
            )
            ->where('ISNULL(rewriting_url.id)')
        ;
        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $category) {
            $loopResultRow = (new LoopResultRow($category))
                ->set('ID', $category->getId())
                ->set('NAME', $category->getTitle());

            if (property_exists($category, 'ref')) {
                $loopResultRow->set('REF', $category->getRef());
            }
            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}