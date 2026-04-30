<?php

namespace RewriteUrl\Service;

use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlGoneUrl;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use RewriteUrl\Model\RewriteurlRuleParam;
use RewriteUrl\Model\RewriteurlRuleParamQuery;
use Thelia\Model\RewritingUrl;
use Thelia\Model\RewritingUrlQuery;

class ImportRewriteUrlService
{
    public function formatAndDecodeUrl(string $url): string
    {
        if (preg_match('#^https?://[^/]+(/.*)$#is', $url, $matches)) {
            $url = $matches[1];
        }

        return urldecode(ltrim($url, '/'));
    }

    public function hasQueryString(string $url): bool
    {
        return str_contains($url, '?');
    }

    public function checkValidUrl(string $url): bool
    {
        if (preg_match('/[\s<>{}|\\\\^`]/', $url)) {
            return false;
        }

        return $url !== '';
    }

    /**
     * @throws PropelException
     */
    public function importGoneUrl(string $url): void
    {
        $existing = RewriteurlGoneUrlQuery::create()
            ->filterByUrlSource($url)
            ->findOne();

        if (null === $existing) {
            $goneUrl = new RewriteurlGoneUrl();
            $goneUrl
                ->setUrlSource($url)
                ->save();
        }
    }

    /**
     * @throws PropelException
     */
    public function importRewriteUrl(string $url, string $redirect): void
    {
        if ($url === $redirect) {
            throw new PropelException('Redirect url cannot be the same as source url');
        }

        $redirectingUrl = RewritingUrlQuery::create()
            ->filterByUrl($redirect)
            ->findOne();

        if (null === $redirectingUrl) {
            $this->importRewriteRuleUrl($url, $redirect);
            return;
        }

        $rewritingUrl = RewritingUrlQuery::create()
            ->filterByUrl($url)
            ->findOne();

        if (null === $rewritingUrl) {
            $rewritingUrl = new RewritingUrl();
            $rewritingUrl->setUrl($url)
                ->setView($redirectingUrl->getView())
                ->setViewId($redirectingUrl->getViewId())
                ->setViewLocale($redirectingUrl->getViewLocale())
                ->save();
        }

        if (null === $rewritingUrl->getRedirected()) {
            $rewritingUrl->setRedirected($redirectingUrl->getId());
            $rewritingUrl->save();
        }
    }

    /**
     * @throws PropelException
     */
    public function importRewriteRuleUrl(string $url, string $redirect): void
    {
        $rewriteurlRule = RewriteurlRuleQuery::create()
            ->filterByRuleType(RewriteurlRule::TYPE_TEXT)
            ->filterByValue($url)
            ->filterByRedirectUrl($redirect)
            ->findOneOrCreate();

        $rewriteurlRule->save();
    }

    private function escapeRegexUrl(string $url): string
    {
        return preg_quote($url, '/');
    }

    /**
     * @throws PropelException
     */
    public function importRewriteRuleUrlWithParams(string $url, string $redirect): void
    {
        $parsed = parse_url($url);
        $path = ltrim($parsed['path'] ?? $url, '/');

        $rewriteurlRule = RewriteurlRuleQuery::create()
            ->filterByRuleType(RewriteurlRule::TYPE_REGEX_GET_PARAMS)
            ->filterByValue($this->escapeRegexUrl($path))
            ->filterByRedirectUrl($redirect)
            ->findOneOrCreate();

        $rewriteurlRule->save();

        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);

            foreach ($queryParams as $name => $value) {
                $param = RewriteurlRuleParamQuery::create()
                    ->filterByIdRule($rewriteurlRule->getId())
                    ->filterByParamName($name)
                    ->findOneOrCreate();

                $param
                    ->setParamCondition(RewriteurlRuleParam::PARAM_CONDITION_EQUALS)
                    ->setParamValue($value)
                    ->save();
            }
        }
    }
}