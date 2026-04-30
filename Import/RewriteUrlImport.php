<?php

namespace RewriteUrl\Import;

use Propel\Runtime\Exception\PropelException;
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

        $url      = trim($data[self::COL_URL] ?? '');
        $redirect = trim($data[self::COL_REDIRECT] ?? '');
        $gone     = trim($data[self::COL_GONE] ?? '');

        if (empty($url)) {
            return Translator::getInstance()->trans('Column URL is empty.', [], RewriteUrl::MODULE_DOMAIN);
        }

        $url = $this->importRewriteUrlService->formatAndDecodeUrl($url);
        $redirect = $this->importRewriteUrlService->formatAndDecodeUrl($redirect);

        if (!$this->importRewriteUrlService->checkValidUrl($url)) {
            return Translator::getInstance()->trans('Column URL is not a valid URL : "%url%".', ['%url%' => $url], RewriteUrl::MODULE_DOMAIN);
        }

        if (!empty($redirect) && !$this->importRewriteUrlService->checkValidUrl($redirect)) {
            return Translator::getInstance()->trans('Column REDIRECT is not a valid URL: "%url%".', ['%url%' => $redirect], RewriteUrl::MODULE_DOMAIN);
        }

        try {
            if (!empty($gone)) {
                $this->importRewriteUrlService->importGoneUrl($gone);
                ++$this->importedRows;
                return null;
            }

            if (!empty($redirect)) {
                $this->importRewriteUrlService->importRewriteUrl($url, $redirect);
                ++$this->importedRows;
                return null;
            }

            $this->importRewriteUrlService->importRewriteRuleUrl($url, '/');
            ++$this->importedRows;
            return null;
        } catch (PropelException $e) {
            return Translator::getInstance()->trans(
                'Error while processing "%url%": %msg%',
                ['%url%' => $url, '%msg%' => $e->getMessage()],
                RewriteUrl::MODULE_DOMAIN
            );
        }
    }
}