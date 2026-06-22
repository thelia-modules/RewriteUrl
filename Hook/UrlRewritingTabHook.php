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

namespace RewriteUrl\Hook;

use Propel\Runtime\ActiveQuery\Criteria;
use RewriteUrl\Model\RewritingRedirectType;
use RewriteUrl\Model\RewritingRedirectTypeQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\Base\RewritingUrlQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\LangQuery;

/**
 * Renders the URL rewriting management UI (redirects 301/302) inside the
 * "Modules" tab of the product, category, folder, content and brand edit
 * pages of the default-twig back office.
 */
class UrlRewritingTabHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            'product.tab-content' => [['type' => 'back', 'method' => 'onProductTabContent']],
            'category.tab-content' => [['type' => 'back', 'method' => 'onCategoryTabContent']],
            'folder.tab-content' => [['type' => 'back', 'method' => 'onFolderTabContent']],
            'content.tab-content' => [['type' => 'back', 'method' => 'onContentTabContent']],
            'brand.tab-content' => [['type' => 'back', 'method' => 'onBrandTabContent']],
        ];
    }

    public function onProductTabContent(HookRenderEvent $event): void
    {
        $this->renderTab($event, 'product', (int) $event->getArgument('product'));
    }

    public function onCategoryTabContent(HookRenderEvent $event): void
    {
        $this->renderTab($event, 'category', (int) $event->getArgument('category'));
    }

    public function onFolderTabContent(HookRenderEvent $event): void
    {
        $this->renderTab($event, 'folder', (int) $event->getArgument('folder'));
    }

    public function onContentTabContent(HookRenderEvent $event): void
    {
        $this->renderTab($event, 'content', (int) $event->getArgument('content'));
    }

    public function onBrandTabContent(HookRenderEvent $event): void
    {
        $this->renderTab($event, 'brand', (int) $event->getArgument('brand'));
    }

    private function renderTab(HookRenderEvent $event, string $view, int $viewId): void
    {
        if ($viewId <= 0) {
            return;
        }

        $event->add($this->render('RewriteUrl/tab-module.html.twig', [
            'view' => $view,
            'view_id' => $viewId,
            'urls' => $this->collectUrls($view, $viewId, redirected: false),
            'redirected' => $this->collectUrls($view, $viewId, redirected: true),
            'langs' => $this->collectLangs(),
            'url_site' => rtrim((string) ConfigQuery::read('url_site', ''), '/'),
            'success_url' => $this->buildSuccessUrl(),
        ]));
    }

    /**
     * @return list<array{id: int, locale: string, lang: string, url: string, redirected: ?string, httpcode: int}>
     */
    private function collectUrls(string $view, int $viewId, bool $redirected): array
    {
        $query = RewritingUrlQuery::create()
            ->filterByView($view)
            ->filterByViewId($viewId)
            ->filterByRedirected(null, $redirected ? Criteria::NOT_EQUAL : Criteria::EQUAL)
            ->orderById();

        $rows = [];
        foreach ($query->find() as $url) {
            $httpcode = RewritingRedirectType::DEFAULT_REDIRECT_TYPE;
            if ($redirected) {
                $type = RewritingRedirectTypeQuery::create()->findPk($url->getId());
                if ($type !== null) {
                    $httpcode = (int) $type->getHttpcode();
                }
            }

            $locale = (string) $url->getViewLocale();
            $rows[] = [
                'id' => (int) $url->getId(),
                'locale' => $locale,
                'lang' => explode('_', $locale)[0],
                'url' => (string) $url->getUrl(),
                'redirected' => $url->getRedirected(),
                'httpcode' => $httpcode,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{locale: string, title: string}>
     */
    private function collectLangs(): array
    {
        $langs = [];
        foreach (LangQuery::create()->orderByPosition()->find() as $lang) {
            $langs[] = ['locale' => (string) $lang->getLocale(), 'title' => (string) $lang->getTitle()];
        }

        return $langs;
    }

    private function buildSuccessUrl(): string
    {
        $request = $this->getRequest();
        if ($request === null) {
            return '';
        }

        $params = $request->query->all();
        $params['current_tab'] = 'modules';

        return $request->getPathInfo().'?'.http_build_query($params);
    }
}
