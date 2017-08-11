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

namespace RewriteUrl\Model;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\RewritingUrl;
use RewriteUrl\Model\RewritingRedirectType as ChildRewritingRedirectType;

/**
 * Class RewritingUrlOverride
 * @package RewriteUrl\Model
 * @author Gilles Bourgeat <gilles.bourgeat@gmail.com>
 */
class RewritingUrlOverride extends RewritingUrl
{
    /**
     * disable the Thelia behavior
     *
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
    }
}
