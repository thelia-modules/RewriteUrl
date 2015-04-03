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

namespace RewriteUrl\Event;

/**
 * Class RewriteUrlEvents
 * @package RewriteUrl\Event
 * @author Vincent Lopes <vlopes@openstudio.fr>
 */
class RewriteUrlEvents
{
    const REWRITEURL_ADD = 'rewriteurl.action.add';
    const REWRITEURL_DELETE = "rewriteurl.action.delete";
    const REWRITEURL_UPDATE = "rewriteurl.action.update";
    const REWRITEURL_SET_DEFAULT = "rewriteurl.action.setdefault";
}
