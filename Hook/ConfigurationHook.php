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

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\ConfigQuery;

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
}
