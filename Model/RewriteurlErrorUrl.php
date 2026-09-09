<?php

namespace RewriteUrl\Model;

use RewriteUrl\Model\Base\RewriteurlErrorUrl as BaseRewriteurlErrorUrl;

/**
 * Skeleton subclass for representing a row from the 'rewriteurl_error_url' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class RewriteurlErrorUrl extends BaseRewriteurlErrorUrl
{
    /**
     * Column widths, mirroring Config/schema.xml. The values stored here come straight
     * from the request and have no length limit of their own: whoever writes a row must
     * cut them to these lengths first, or the insert fails with SQLSTATE 22001.
     */
    public const URL_SOURCE_MAX_LENGTH = 1024;

    public const USER_AGENT_MAX_LENGTH = 512;
}
