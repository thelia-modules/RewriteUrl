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

use Thelia\Controller\Admin\AdminController;
use Thelia\Core\HttpFoundation\Request;

/**
 * Class NotRewrittenUrlsAdminController
 * @package RewriteUrl\Controller\Admin
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class NotRewritenUrlsAdminController extends AdminController
{
    public function defaultAction(Request $request)
    {
        return $this->render(
            'list-notrewritenurls',
            array(
                'current_tab' => $request->get('current_tab', 'categories'),
                'page_category' => $request->get('page_category', 1),
                'page_product' => $request->get('page_product', 1),
                'page_brand' => $request->get('page_brand', 1),
                'page_folder' => $request->get('page_folder', 1),
                'page_content' => $request->get('page_content', 1),
                )
        );
    }
}
