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

namespace RewriteUrl\Controller\Admin;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Thelia\Controller\Admin\AdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\LangQuery;
use Thelia\Model\Map\RewritingUrlTableMap;
use Thelia\Model\RewritingUrlQuery;

/**
 * Class NotRewrittenUrlsAdminController
 * @package RewriteUrl\Controller\Admin
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class NotRewritenUrlsAdminController extends AdminController
{
    private const PAGE_SIZE = 10;

    public function defaultAction(Request $request)
    {
        $currentTab = $request->query->get('current_tab', 'categories');
        $editLanguageId = $request->query->get('edit_language_id');
        $locale = $this->resolveLocale($editLanguageId);

        $views = [
            'category' => (int) $request->query->get('page_category', 1),
            'product' => (int) $request->query->get('page_product', 1),
            'brand' => (int) $request->query->get('page_brand', 1),
            'folder' => (int) $request->query->get('page_folder', 1),
            'content' => (int) $request->query->get('page_content', 1),
        ];

        $lists = [];
        $counts = [];

        foreach ($views as $view => $page) {
            $page = max(1, $page);
            $baseQuery = $this->buildNotRewritenQuery($view, $locale);
            $counts[$view] = (clone $baseQuery)->count();

            $rows = $baseQuery
                ->offset(($page - 1) * self::PAGE_SIZE)
                ->limit(self::PAGE_SIZE)
                ->find();

            $items = [];
            foreach ($rows as $entity) {
                $item = [
                    'ID' => $entity->getId(),
                    'NAME' => $entity->setLocale($locale)->getTitle(),
                ];
                if (method_exists($entity, 'getRef')) {
                    $item['REF'] = $entity->getRef();
                }
                $items[] = $item;
            }

            $lists[$view] = [
                'items' => $items,
                'page' => $page,
                'pageCount' => (int) ceil(max(1, $counts[$view]) / self::PAGE_SIZE),
            ];
        }

        return $this->render(
            'list-notrewritenurls',
            [
                'current_tab' => $currentTab,
                'edit_language_id' => $editLanguageId,
                'langs' => $this->buildLangList(),
                'lists' => $lists,
                'counts' => $counts,
            ]
        );
    }

    private function buildNotRewritenQuery(string $view, string $locale): ModelCriteria
    {
        $class = 'Thelia\\Model\\' . ucfirst($view) . 'Query';
        /** @var ModelCriteria $objectQuery */
        $objectQuery = $class::create();

        $rewritenIds = RewritingUrlQuery::create()
            ->filterByView($view)
            ->filterByViewLocale($locale)
            ->select([RewritingUrlTableMap::COL_VIEW_ID])
            ->groupBy(RewritingUrlTableMap::COL_VIEW_ID)
            ->find()
            ->getData();

        return $objectQuery->filterById($rewritenIds, Criteria::NOT_IN);
    }

    private function resolveLocale(?string $editLanguageId): string
    {
        $lang = null;
        if ($editLanguageId !== null) {
            $lang = LangQuery::create()->findPk((int) $editLanguageId);
        }
        if ($lang === null) {
            $lang = LangQuery::create()->findOneByByDefault(1);
        }

        return $lang !== null ? $lang->getLocale() : 'en_US';
    }

    /**
     * @return array<int, array{id: int, title: string, code: string}>
     */
    private function buildLangList(): array
    {
        $langs = [];
        foreach (LangQuery::create()->find() as $lang) {
            $langs[] = [
                'id' => $lang->getId(),
                'title' => $lang->getTitle(),
                'code' => $lang->getCode(),
            ];
        }

        return $langs;
    }
}
