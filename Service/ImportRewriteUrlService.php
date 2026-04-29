<?php

namespace RewriteUrl\Service;

use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlGoneUrl;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
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
            $this->importRewriteRuleUrl($redirect, $url);
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
}