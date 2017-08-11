<?php

namespace RewriteUrl\Model;

use RewriteUrl\Model\Base\RewritingRedirectType as BaseRewritingRedirectType;

class RewritingRedirectType extends BaseRewritingRedirectType
{
    const TEMPORARY = 301;
    const PERMANENT = 302;

    /**
     * Default redirect type for a URL if there is no matching row in the RewritingRedirectType table
     */
    const DEFAULT_REDIRECT_TYPE = RewritingRedirectType::TEMPORARY;
}
