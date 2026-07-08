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

use RewriteUrl\RewriteUrl;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;

/**
 * Class ConfigurationHook
 * @package RewriteUrl\Hook
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class ConfigurationHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            'configuration.catalog-top' => [
                ['type' => 'back', 'method' => 'onConfigurationCatalogTop'],
            ],
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
            'module.config-js' => [
                ['type' => 'back', 'method' => 'onModuleConfigurationJavascript'],
            ],
            'main.top-menu-tools' => [
                ['type' => 'back', 'method' => 'onMainTopMenuTools'],
            ],
        ];
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        $extension = $this->getParser()->getFileExtension();

        $event->add(
            $this->render(
                'RewriteUrl/module-configuration.'.$extension,
                [
                    "isRewritingEnabled" => ConfigQuery::isRewritingEnable()
                ]
            )
        );
    }

    public function onModuleConfigurationJavascript(HookRenderEvent $event): void
    {
        $extension = $this->getParser()->getFileExtension();

        $event->add(
            $this->render(
                'RewriteUrl/module-configuration-js.'.$extension,
                [
                    "isRewritingEnabled" => ConfigQuery::isRewritingEnable()
                ]
            )
        );
    }

    public function onConfigurationCatalogTop(HookRenderEvent $event): void
    {
        $event->add($this->render(
            'configuration-catalog.'.$this->getParser()->getFileExtension()
        ));
    }

    public function onMainTopMenuTools(HookRenderBlockEvent $event)
    {
        $event->add(
            [
                'id' => 'tools_menu_rewriteurl',
                'class' => '',
                'url' => URL::getInstance()?->absoluteUrl('/admin/module/RewriteUrl'),
                'title' => $this->trans('Global URL Rewriting', [], RewriteUrl::MODULE_DOMAIN),
            ],
        );
        $event->add(
            [
                'id' => 'tools_menu_rewriteurl_error',
                'class' => '',
                'url' => URL::getInstance()?->absoluteUrl('/admin/module/RewriteUrl/manageErrorUrl'),
                'title' => $this->trans('Manage 404 Errors Url', [], RewriteUrl::MODULE_DOMAIN),
            ],
        );
    }
}
