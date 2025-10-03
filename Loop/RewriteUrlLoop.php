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
use Propel\Runtime\ActiveQuery\ModelCriteria;
use RewriteUrl\Model\RewritingRedirectType;
use RewriteUrl\Model\RewritingRedirectTypeQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Model\Base\RewritingUrlQuery;

/**
 * Class RewriteUrlLoop
 * @package RewriteUrl\Loop
 * @author Vincent Lopes <vlopes@openstudio.fr>
 * @author Gilles Bourgeat <gbourgeat@openstudio.fr>
 */
class RewriteUrlLoop extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions(): ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('id'),
            Argument::createAnyTypeArgument('view'),
            Argument::createIntTypeArgument('view_id'),
            Argument::createIntTypeArgument('redirect')
        );
    }

    public function buildModelCriteria(): ModelCriteria
    {
        $search = RewritingUrlQuery::create();

        if (null !== $id = $this->getId()) {
            $search->filterById($id);
        }

        if (null !== $view_id = $this->getViewId()) {
            $search->filterByViewId($view_id)->filterByView($this->getView())->find();
        }

        $redirect = $this->getRedirect();
        if ($redirect == 1) {
            $search->filterByRedirected(null, Criteria::NOT_EQUAL)->find();
        } elseif ($redirect == 0) {
            $search->filterByRedirected(null, Criteria::EQUAL)->find();
        }

        return $search;
    }

    public function parseResults(LoopResult $loopResult): LoopResult
    {
        $redirectTypeSearch = RewritingRedirectTypeQuery::create();
        foreach ($loopResult->getResultDataCollection() as $rewriteURl) {
            $loopResultRow = (new LoopResultRow($rewriteURl))
                ->set('ID_URL', $rewriteURl->getID())
                ->set('URL', $rewriteURl->getUrl())
                ->set('VIEW', $rewriteURl->getView())
                ->set('VIEW_LOCALE', $rewriteURl->getViewLocale())
                ->set('REDIRECTED', $rewriteURl->getRedirected())
                ->set('VIEW_ID', $rewriteURl->getViewId());


            if ($rewriteURl->getRedirected()) {
                $redirectType = $redirectTypeSearch->findPk($rewriteURl->getID());
                if ($redirectType == null) {
                    $httpcode = RewritingRedirectType::DEFAULT_REDIRECT_TYPE;
                } else {
                    $httpcode = $redirectType->getHttpcode();
                }
                $loopResultRow->set('HTTPCODE', $httpcode);
            }

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
