<?php

namespace RewriteUrl\Service;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Exception\ImportUrlConflictException;
use RewriteUrl\Model\RewriteurlGoneUrl;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use RewriteUrl\Model\RewriteurlRule;
use RewriteUrl\Model\RewriteurlRuleQuery;
use RewriteUrl\Model\RewriteurlRuleParam;
use RewriteUrl\Model\RewriteurlRuleParamQuery;
use RewriteUrl\RewriteUrl;
use Thelia\Core\Translation\Translator;
use Thelia\Model\BrandQuery;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ContentQuery;
use Thelia\Model\FolderQuery;
use Thelia\Model\ProductQuery;
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
     * @throws ImportUrlConflictException
     */
    public function importGoneUrl(string $url): void
    {
        // A redirection always wins over a gone URL (redirections are resolved before the
        // 404 handler which sends the 410), so declaring both for the same URL would
        // silently discard the 410.
        if (null !== $this->findTextRule($url)) {
            throw new ImportUrlConflictException(
                $this->trans(
                    'URL "%url%" is already redirected by a rule: remove that rule before declaring it as gone (410).',
                    ['%url%' => $url]
                )
            );
        }

        $rewritingUrl = RewritingUrlQuery::create()->filterByUrl($url)->findOne();

        if (null !== $rewritingUrl && null !== $rewritingUrl->getRedirected()) {
            throw new ImportUrlConflictException(
                $this->trans(
                    'URL "%url%" is already redirected by Thelia: remove that redirection before declaring it as gone (410).',
                    ['%url%' => $url]
                )
            );
        }

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
     * @return string|null Warning about the way the redirection had to be done, if any
     *
     * @throws PropelException
     * @throws ImportUrlConflictException
     */
    public function importRewriteUrl(string $url, string $redirect): ?string
    {
        if ($url === $redirect) {
            throw new ImportUrlConflictException($this->trans('Redirect url cannot be the same as source url'));
        }

        if (null !== RewriteurlGoneUrlQuery::create()->filterByUrlSource($url)->findOne()) {
            throw new ImportUrlConflictException(
                $this->trans(
                    'URL "%url%" is declared as gone (410): remove it from the gone URLs before redirecting it.',
                    ['%url%' => $url]
                )
            );
        }

        $targetRewritingUrl = $this->resolveRedirectTarget($redirect);
        $redirectUrl = null !== $targetRewritingUrl ? $targetRewritingUrl->getUrl() : $redirect;

        if ($redirectUrl === $url) {
            throw new ImportUrlConflictException(
                $this->trans(
                    'Redirect url "%url%" ends up on the source url: this would create a redirection loop.',
                    ['%url%' => $redirect]
                )
            );
        }

        $sourceRewritingUrl = RewritingUrlQuery::create()->filterByUrl($url)->findOne();

        // The URL is already known by Thelia: it belongs to a product, a category, a
        // content, ... Flagging it as redirected would take it away from that object, and
        // if it was its last URL the object has no canonical URL any more, which breaks
        // the front (error 500 while resolving the URL) and the URL generation in the
        // back-office. A text rule redirects the URL without touching the rewritten URLs
        // at all, and stays reversible (it can be deleted from the rules page).
        if (null !== $sourceRewritingUrl) {
            if ($this->isAlreadyRedirectedTo($sourceRewritingUrl, $redirectUrl)) {
                return null;
            }

            // Redirecting the URL of a page which is still online would make that page
            // unreachable: such a URL is not a 404, so it is very likely a mistake. Only
            // the alternative URLs of an online object can be redirected.
            $isTheUrlOfTheObject = null === $sourceRewritingUrl->getRedirected()
                || null === $this->findCanonicalUrl($sourceRewritingUrl);

            if ($isTheUrlOfTheObject && true === $this->isVisible($sourceRewritingUrl)) {
                throw new ImportUrlConflictException(
                    $this->trans(
                        'URL "%url%" is the URL of the %view% #%viewId%, which is online: redirecting it would make that page unreachable.',
                        [
                            '%url%' => $url,
                            '%view%' => $sourceRewritingUrl->getView(),
                            '%viewId%' => $sourceRewritingUrl->getViewId(),
                        ]
                    )
                );
            }

            $this->importRewriteRuleUrl($url, $redirectUrl);
            $this->restoreCanonicalUrl($sourceRewritingUrl);

            // The rule redirects the URL whatever the state of the object it belongs to:
            // it has to be deleted if that object is put back online one day.
            return $this->trans(
                'A redirection rule has been created for "%url%", which is the URL of the %view% #%viewId%: if that %view% is put back online, delete the rule, otherwise its page will stay redirected.',
                [
                    '%url%' => $url,
                    '%view%' => $sourceRewritingUrl->getView(),
                    '%viewId%' => $sourceRewritingUrl->getViewId(),
                ]
            );
        }

        // The source URL is unknown: Thelia can handle the redirection natively. But the
        // router resolves the destination from the view/view_id/locale of the source row,
        // not from the `redirected` foreign key, so this is only reliable when the target
        // object has exactly one canonical URL. Otherwise, fall back on a text rule which
        // always redirects to the requested URL.
        if (null === $targetRewritingUrl || 1 !== $this->countCanonicalUrls($targetRewritingUrl)) {
            $this->importRewriteRuleUrl($url, $redirectUrl);

            return null;
        }

        $rewritingUrl = new RewritingUrl();
        $rewritingUrl
            ->setUrl($url)
            ->setView($targetRewritingUrl->getView())
            ->setViewId($targetRewritingUrl->getViewId())
            ->setViewLocale($targetRewritingUrl->getViewLocale())
            ->save();

        // Saved in two steps on purpose: when `redirected` is set on insert,
        // RewritingUrl::postInsert() flags every other URL of the same object as
        // redirected to the inserted one, which would rewire unrelated redirections.
        $rewritingUrl
            ->setRedirected($targetRewritingUrl->getId())
            ->save();

        return null;
    }

    /**
     * @throws PropelException
     */
    public function importRewriteRuleUrl(string $url, string $redirect): void
    {
        $rewriteurlRule = $this->findTextRule($url);

        if (null === $rewriteurlRule) {
            $rewriteurlRule = new RewriteurlRule();
            $rewriteurlRule
                ->setRuleType(RewriteurlRule::TYPE_TEXT)
                ->setValue($url)
                ->setOnly404(false);

            $rewriteurlRule->setPosition($rewriteurlRule->getNextPosition());
        }

        // Updated instead of duplicated: two text rules on the same URL would make the
        // destination depend on their position, and the oldest one would win.
        $rewriteurlRule
            ->setRedirectUrl($redirect)
            ->save();
    }

    /**
     * @throws PropelException
     */
    public function importRewriteRuleUrlWithParams(string $url, string $redirect): void
    {
        $parsed = parse_url($url);
        $path = $this->escapeRegexUrl(ltrim($parsed['path'] ?? $url, '/'));
        $queryParams = [];

        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }

        $rewriteurlRule = $this->findRegexParamsRule($path, $redirect, $queryParams);

        if (null === $rewriteurlRule) {
            $rewriteurlRule = new RewriteurlRule();
            $rewriteurlRule
                ->setRuleType(RewriteurlRule::TYPE_REGEX_GET_PARAMS)
                ->setValue($path)
                ->setRedirectUrl($redirect)
                ->setOnly404(false);

            $rewriteurlRule->setPosition($rewriteurlRule->getNextPosition());
        }

        $rewriteurlRule->save();

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

    /**
     * The regex is anchored on the whole path, otherwise importing "presse-ail" would also
     * redirect "presse-ail-inox" and every other URL containing the imported one.
     */
    private function escapeRegexUrl(string $url): string
    {
        return '^\/?' . preg_quote($url, '/') . '$';
    }

    /**
     * Returns the rewritten URL the redirection must finally point to, or null when the
     * target is not a rewritten URL (a route such as /cart, an external URL, ...).
     *
     * @throws ImportUrlConflictException
     */
    private function resolveRedirectTarget(string $redirect): ?RewritingUrl
    {
        $rewritingUrl = RewritingUrlQuery::create()->filterByUrl($redirect)->findOne();

        if (null === $rewritingUrl) {
            return null;
        }

        if (null === $rewritingUrl->getRedirected()) {
            $this->checkTargetIsOnline($rewritingUrl, $redirect);

            return $rewritingUrl;
        }

        // The target is itself a redirection: point directly to the canonical URL of its
        // object, to avoid chaining two redirections.
        $canonicalUrl = $this->findCanonicalUrl($rewritingUrl);

        if (null === $canonicalUrl) {
            throw new ImportUrlConflictException(
                $this->trans(
                    'Redirect url "%url%" is itself redirected, and the %view% #%viewId% it belongs to has no canonical URL: fix that URL in the back-office first.',
                    [
                        '%url%' => $redirect,
                        '%view%' => $rewritingUrl->getView(),
                        '%viewId%' => $rewritingUrl->getViewId(),
                    ]
                )
            );
        }

        $this->checkTargetIsOnline($canonicalUrl, $redirect);

        return $canonicalUrl;
    }

    /**
     * Redirecting to a page which is offline just moves the 404 somewhere else.
     *
     * @throws ImportUrlConflictException
     */
    private function checkTargetIsOnline(RewritingUrl $rewritingUrl, string $redirect): void
    {
        if (false !== $this->isVisible($rewritingUrl)) {
            return;
        }

        throw new ImportUrlConflictException(
            $this->trans(
                'Redirect url "%url%" points to the %view% #%viewId%, which is offline: the redirection would end up on a 404.',
                [
                    '%url%' => $redirect,
                    '%view%' => $rewritingUrl->getView(),
                    '%viewId%' => $rewritingUrl->getViewId(),
                ]
            )
        );
    }

    /**
     * Returns null when the visibility of the object cannot be checked (unknown view, or
     * object already deleted).
     */
    private function isVisible(RewritingUrl $rewritingUrl): ?bool
    {
        $viewId = (int) $rewritingUrl->getViewId();

        $object = match ($rewritingUrl->getView()) {
            'product' => ProductQuery::create()->findPk($viewId),
            'category' => CategoryQuery::create()->findPk($viewId),
            'content' => ContentQuery::create()->findPk($viewId),
            'folder' => FolderQuery::create()->findPk($viewId),
            'brand' => BrandQuery::create()->findPk($viewId),
            default => null,
        };

        if (null === $object) {
            return null;
        }

        return (bool) $object->getVisible();
    }

    private function findCanonicalUrl(RewritingUrl $rewritingUrl): ?RewritingUrl
    {
        return $this->canonicalUrlQuery($rewritingUrl)->findOne();
    }

    private function countCanonicalUrls(RewritingUrl $rewritingUrl): int
    {
        return $this->canonicalUrlQuery($rewritingUrl)->count();
    }

    private function canonicalUrlQuery(RewritingUrl $rewritingUrl): RewritingUrlQuery
    {
        return RewritingUrlQuery::create()
            ->filterByView($rewritingUrl->getView())
            ->filterByViewId($rewritingUrl->getViewId())
            ->filterByViewLocale($rewritingUrl->getViewLocale())
            ->filterByRedirected(null, Criteria::ISNULL);
    }

    /**
     * Thelia redirects to the canonical URL of the object the source URL belongs to, so
     * the redirection is already in place when that canonical URL is the expected target.
     */
    private function isAlreadyRedirectedTo(RewritingUrl $sourceRewritingUrl, string $redirect): bool
    {
        if (null === $sourceRewritingUrl->getRedirected()) {
            return false;
        }

        $canonicalUrl = $this->findCanonicalUrl($sourceRewritingUrl);

        // The object has no canonical URL left: the redirection is broken, whatever its
        // target, and has to be replaced by a rule.
        if (null === $canonicalUrl) {
            return false;
        }

        return $canonicalUrl->getUrl() === $redirect;
    }

    /**
     * A previous import may have flagged the URL as redirected, which took it away from
     * its object and left that object without any canonical URL. The redirection is now
     * handled by a rule, so the URL is given back to its object.
     *
     * @throws PropelException
     */
    private function restoreCanonicalUrl(RewritingUrl $rewritingUrl): void
    {
        if (null === $rewritingUrl->getRedirected() || null !== $this->findCanonicalUrl($rewritingUrl)) {
            return;
        }

        $rewritingUrl
            ->setRedirected(null)
            ->save();
    }

    /**
     * Rules created before this version are stored with a leading slash, and are matched
     * against the path info as is: they are looked up as well, to update them instead of
     * adding a second rule for the same URL.
     */
    private function findTextRule(string $url): ?RewriteurlRule
    {
        return RewriteurlRuleQuery::create()
            ->filterByRuleType(RewriteurlRule::TYPE_TEXT)
            ->filterByValue([$url, '/' . $url])
            ->orderByPosition()
            ->findOne();
    }

    /**
     * A rule is reused only when its GET parameters are the same, otherwise the parameters
     * of both imported lines would be merged into a single rule that never matches.
     */
    private function findRegexParamsRule(string $value, string $redirect, array $queryParams): ?RewriteurlRule
    {
        $rules = RewriteurlRuleQuery::create()
            ->filterByRuleType(RewriteurlRule::TYPE_REGEX_GET_PARAMS)
            ->filterByValue($value)
            ->filterByRedirectUrl($redirect)
            ->orderByPosition()
            ->find();

        /** @var RewriteurlRule $rule */
        foreach ($rules as $rule) {
            $ruleParams = [];

            foreach (RewriteurlRuleParamQuery::create()->filterByIdRule($rule->getId())->find() as $ruleParam) {
                $ruleParams[$ruleParam->getParamName()] = $ruleParam->getParamValue();
            }

            if ($ruleParams == $queryParams) {
                return $rule;
            }
        }

        return null;
    }

    private function trans(string $id, array $parameters = []): string
    {
        return Translator::getInstance()->trans($id, $parameters, RewriteUrl::MODULE_DOMAIN);
    }
}
