<?php

namespace RewriteUrl\Model;

use RewriteUrl\Model\Base\RewriteurlErrorUrlReferer as BaseRewriteurlErrorUrlReferer;

/**
 * Skeleton subclass for representing a row from the 'rewriteurl_error_url_referer' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class RewriteurlErrorUrlReferer extends BaseRewriteurlErrorUrlReferer
{
    /**
     * Column width, mirroring Config/schema.xml. See RewriteurlErrorUrl for why the
     * caller has to cut the value first.
     */
    public const REFERER_MAX_LENGTH = 1024;
}
