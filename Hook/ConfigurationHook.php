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

use Carousel\Carousel;
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
    public function onModuleConfiguration(HookRenderEvent $event)
    {
        $event->add(
            $this->render(
                'RewriteUrl/module-configuration.html',
                [
                    "isRewritingEnabled" => ConfigQuery::isRewritingEnable()
                ]
            )
        );
    }

    public function onModuleConfigurationJavascript(HookRenderEvent $event)
    {
        $event->add(
            $this->render(
                'RewriteUrl/module-configuration-js.html',
                [
                    "isRewritingEnabled" => ConfigQuery::isRewritingEnable()
                ]
            )
        );
    }

    public function onConfigurationCatalogTop(HookRenderEvent $event)
    {
        $event->add($this->render(
            'configuration-catalog.html'
        ));
    }

    public function onMainTopMenuTools(HookRenderBlockEvent $event)
    {
        $event->add(
            [
                'id' => 'tools_menu_rewriteutl',
                'class' => '',
                'url' => URL::getInstance()->absoluteUrl('/admin/module/RewriteUrl'),
                'title' => $this->trans('Global URL Rewriting', [], RewriteUrl::MODULE_DOMAIN),
            ]
        );
    }
}
