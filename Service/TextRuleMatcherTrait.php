<?php

namespace RewriteUrl\Service;

use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;

trait TextRuleMatcherTrait
{
    /**
     * The path info of the request keeps its percent encoded characters, while text rules
     * are stored decoded, the way they are typed in the back-office and imported. Both
     * forms are looked up so that a URL containing accents is matched as well.
     */
    protected function findTextRule(int $only404, string $pathInfo): ?RewriteurlRule
    {
        $paths = [ltrim($pathInfo, '/')];
        $decodedPath = urldecode($paths[0]);

        if ($decodedPath !== $paths[0]) {
            $paths[] = $decodedPath;
        }

        return RewriteurlRuleQuery::create()
            ->filterByOnly404($only404)
            ->filterByRuleType(RewriteurlRule::TYPE_TEXT)
            ->filterByValue($paths)
            ->orderByPosition()
            ->findOne();
    }
}
