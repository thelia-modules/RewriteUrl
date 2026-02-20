<?php

namespace RewriteUrl\Import;

use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Model\RewriteurlGoneUrl;
use RewriteUrl\Model\RewriteurlGoneUrlQuery;
use RewriteUrl\RewriteUrl;
use Thelia\Core\Translation\Translator;
use Thelia\ImportExport\Import\AbstractImport;
use Thelia\Model\RewritingUrlQuery;
use Thelia\Model\RewritingUrl;

class RewriteUrlImport extends AbstractImport
{
    const COL_URL         = 'URL';
    const COL_REDIRECT    = 'REDIRECT';
    const COL_GONE        = 'GONE';

    protected $mandatoryColumns = [self::COL_URL, self::COL_REDIRECT, self::COL_GONE];
    /**
     * @param array $data
     * @return string|null Error message or null on success
     */
    public function importData(array $data): ?string
    {
        $url      = trim($data[self::COL_URL] ?? '');
        $redirect = trim($data[self::COL_REDIRECT] ?? '');
        $gone     = trim($data[self::COL_GONE] ?? '');

        if (empty($url)) {
            return Translator::getInstance()->trans('Column URL is empty.', [], RewriteUrl::MODULE_DOMAIN);
        }

        $url = $this->formatAndDecodeUrl($url);
        $redirect = $this->formatAndDecodeUrl($redirect);

        try {
            if (!empty($gone)) {
                $existing = RewriteurlGoneUrlQuery::create()
                    ->filterByUrlSource($url)
                    ->findOne();

                if (null === $existing) {
                    $goneUrl = new RewriteurlGoneUrl();
                    $goneUrl
                        ->setUrlSource($url)
                        ->save();
                }

                ++$this->importedRows;
                return null;
            }

            if (!empty($redirect)) {
                $redirectingUrl = RewritingUrlQuery::create()
                    ->filterByUrl($redirect)
                    ->findOne();

                if (null === $redirectingUrl) {
                    $redirectingUrl = new RewritingUrl();
                    $redirectingUrl
                        ->setUrl($redirect)
                        ->setView('obsolete-rewritten-url')
                        ->setViewId(NULL)
                        ->setViewLocale($this->getLang()->getLocale())
                        ->setRedirected(NULL)
                        ->save();
                }

                $rewritingUrl = RewritingUrlQuery::create()
                    ->filterByUrl($url)
                    ->findOne();

                if (null === $rewritingUrl) {
                    $rewritingUrl = new RewritingUrl();
                    $rewritingUrl->setUrl($url);
                }

                $rewritingUrl
                    ->setView($redirectingUrl->getView())
                    ->setViewId($redirectingUrl->getViewId())
                    ->setViewLocale($redirectingUrl->getViewLocale())
                    ->setRedirected($redirectingUrl->getId());

                    $rewritingUrl->save();

                ++$this->importedRows;
                return null;
            }

            return Translator::getInstance()->trans(
                'Ignored line for "%url%": neither Redirect 301 nor Delete 410 is set.',
                ['%url%' => $url],
                RewriteUrl::MODULE_DOMAIN
            );
        } catch (PropelException $e) {
            return Translator::getInstance()->trans(
                'Error while processing "%url%": %msg%',
                ['%url%' => $url, '%msg%' => $e->getMessage()],
                RewriteUrl::MODULE_DOMAIN
            );
        }
    }

    private function formatAndDecodeUrl(string $url): string
    {
        if (preg_match('#^https?://[^/]+(/.*)$#i', $url, $matches)) {
            $url = $matches[1];
        }

        return urldecode(ltrim($url, '/'));
    }
}