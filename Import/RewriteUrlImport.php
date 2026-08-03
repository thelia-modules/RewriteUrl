<?php

namespace RewriteUrl\Import;

use Propel\Runtime\Exception\PropelException;
use RewriteUrl\Exception\ImportUrlConflictException;
use RewriteUrl\RewriteUrl;
use RewriteUrl\Service\ImportRewriteUrlService;
use Thelia\Core\Translation\Translator;
use Thelia\ImportExport\Import\AbstractImport;
use Thelia\Model\ModuleQuery;

class RewriteUrlImport extends AbstractImport
{
    const COL_URL         = 'URL';
    const COL_REDIRECT    = 'REDIRECT';
    const COL_GONE        = 'GONE';

    protected $mandatoryColumns = [self::COL_URL, self::COL_REDIRECT, self::COL_GONE];

    protected ImportRewriteUrlService $importRewriteUrlService;

    protected int $lineNumber = 0;

    public function __construct()
    {
        $this->importRewriteUrlService = new ImportRewriteUrlService();
    }

    /**
     * @param array $data
     * @return string|null Error message or null on success
     * @throws \Exception
     */
    public function importData(array $data): ?string
    {
        if (ModuleQuery::create()->filterByCode("UrlSanitizer")->filterByActivate(1)->findOne()) {
            throw new \Exception("UrlSanitizer module is activated. Please disable it before importing the file.");
        }

        ++$this->lineNumber;

        $url      = trim($data[self::COL_URL] ?? '');
        $redirect = trim($data[self::COL_REDIRECT] ?? '');
        $gone     = trim($data[self::COL_GONE] ?? '');

        if (empty($url)) {
            return $this->error('Column URL is empty.');
        }

        $url = $this->importRewriteUrlService->formatAndDecodeUrl($url);
        $redirect = $this->importRewriteUrlService->formatAndDecodeUrl($redirect);

        if (!$this->importRewriteUrlService->checkValidUrl($url)) {
            return $this->error('Column URL is not a valid URL : "%url%".', ['%url%' => $url]);
        }

        if (!empty($redirect) && !$this->importRewriteUrlService->checkValidUrl($redirect)) {
            return $this->error('Column REDIRECT is not a valid URL: "%url%".', ['%url%' => $redirect]);
        }

        if (!empty($redirect) && !empty($gone)) {
            return $this->error(
                'Both Redirect 301 and Delete 410 are set for "%url%": keep only one of them.',
                ['%url%' => $url]
            );
        }

        try {
            if (!empty($gone)) {
                // The GONE column is a marker (X, 1, ...): the URL to declare as gone is
                // the one of the URL column, normalized the same way as a redirection.
                $this->importRewriteUrlService->importGoneUrl($url);
                ++$this->importedRows;

                return null;
            }

            // Without a target, the line used to be turned into a redirection to the home
            // page, which silently hides the URL behind a soft 404.
            if (empty($redirect)) {
                return $this->error(
                    'Ignored line for "%url%": neither Redirect 301 nor Delete 410 is set.',
                    ['%url%' => $url]
                );
            }

            if ($this->importRewriteUrlService->hasQueryString($url)) {
                $this->importRewriteUrlService->importRewriteRuleUrlWithParams($url, $redirect);
                ++$this->importedRows;

                return null;
            }

            $this->importRewriteUrlService->importRewriteUrl($url, $redirect);
            ++$this->importedRows;

            return null;
        } catch (ImportUrlConflictException $e) {
            return $this->prefixWithLineNumber($e->getMessage());
        } catch (PropelException $e) {
            return $this->error(
                'Error while processing "%url%": %msg%',
                ['%url%' => $url, '%msg%' => $e->getMessage()]
            );
        }
    }

    protected function error(string $message, array $parameters = []): string
    {
        return $this->prefixWithLineNumber(
            Translator::getInstance()->trans($message, $parameters, RewriteUrl::MODULE_DOMAIN)
        );
    }

    protected function prefixWithLineNumber(string $message): string
    {
        return Translator::getInstance()->trans(
            'Line %line%: %msg%',
            ['%line%' => $this->lineNumber, '%msg%' => $message],
            RewriteUrl::MODULE_DOMAIN
        );
    }
}
