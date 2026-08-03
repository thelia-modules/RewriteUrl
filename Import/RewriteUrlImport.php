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

    /** @var int Number of warnings detailed in the report, the other ones being counted */
    const MAX_DETAILED_WARNINGS = 20;

    protected $mandatoryColumns = [self::COL_URL, self::COL_REDIRECT, self::COL_GONE];

    protected ImportRewriteUrlService $importRewriteUrlService;

    protected int $rowIndex = 0;

    protected int $refusedLines = 0;

    protected int $unreadableLines = 0;

    protected int $completedLines = 0;

    protected int $dataLines = 0;

    /** @var int[] Line number in the file of each row to import */
    protected array $lineNumbers = [];

    /** @var string[] Messages about the lines which could not be read at all */
    protected array $unreadableLineMessages = [];

    /** @var string[] Messages about the lines imported in a way that needs an eye later */
    protected array $warnings = [];

    public function __construct()
    {
        $this->importRewriteUrlService = new ImportRewriteUrlService();
    }

    /**
     * The CSV serializer of Thelia silently drops every line which has not exactly as many
     * columns as the header, so a missing trailing separator or a comma inside an URL makes
     * the line disappear from the import without any message. The file is read again here,
     * to import the lines which can be read without any doubt and to report the other ones.
     */
    public function setData(array $data)
    {
        $rows = $this->readFile();

        if (null === $rows) {
            return parent::setData($data);
        }

        if ([] === $rows) {
            throw new \UnexpectedValueException(
                $this->trans(
                    'No usable line found in the file: check its header (%columns%) and its separator.',
                    ['%columns%' => implode(', ', $this->mandatoryColumns)]
                )
            );
        }

        $this->lineNumbers = array_column($rows, 'line');

        return parent::setData(array_column($rows, 'data'));
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

        ++$this->rowIndex;

        $message = $this->importRow($data);

        if (null !== $message) {
            ++$this->refusedLines;
        }

        if ($this->rowIndex < \count($this->getData())) {
            return $message;
        }

        return $this->appendReport($message);
    }

    protected function importRow(array $data): ?string
    {
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

            $warning = $this->importRewriteUrlService->importRewriteUrl($url, $redirect);
            ++$this->importedRows;

            if (null !== $warning) {
                $this->warnings[] = $this->prefixWithLineNumber($warning);
            }

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

    /**
     * Returns the lines to import with their line number in the file, or null when the file
     * is not a CSV file holding the expected columns, to let Thelia read it.
     */
    protected function readFile(): ?array
    {
        $file = $this->getFile();

        if (null === $file || !is_readable($file->getPathname())) {
            return null;
        }

        $handle = fopen($file->getPathname(), 'r');

        if (false === $handle) {
            return null;
        }

        $firstLine = fgets($handle);

        if (false === $firstLine) {
            fclose($handle);

            return null;
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $headers = null;
        $lineNumber = 0;
        $rows = [];

        while (false !== $columns = fgetcsv($handle, 0, $delimiter)) {
            ++$lineNumber;

            if (!\is_array($columns) || '' === trim(implode('', array_map(static fn ($column) => (string) $column, $columns)))) {
                continue;
            }

            if (null === $headers) {
                $headers = $this->normalizeHeaders($columns);

                if ([] !== array_diff($this->mandatoryColumns, $headers)) {
                    fclose($handle);

                    return null;
                }

                continue;
            }

            ++$this->dataLines;

            if (\count($columns) > \count($headers)) {
                ++$this->unreadableLines;
                $this->unreadableLineMessages[] = $this->trans(
                    'Line %line% could not be read: %found% columns found instead of %expected%, check the separators and the quotes of this line.',
                    [
                        '%line%' => $lineNumber,
                        '%found%' => \count($columns),
                        '%expected%' => \count($headers),
                    ]
                );

                continue;
            }

            if (\count($columns) < \count($headers)) {
                ++$this->completedLines;
                $columns = array_pad($columns, \count($headers), '');
            }

            $rows[] = [
                'line' => $lineNumber,
                'data' => array_combine($headers, $columns),
            ];
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Removes the byte order mark added by spreadsheet softwares, and accepts the columns
     * whatever their case.
     */
    protected function normalizeHeaders(array $columns): array
    {
        $headers = [];

        foreach ($columns as $index => $column) {
            $column = (string) $column;

            if (0 === $index) {
                $column = preg_replace('/^\x{FEFF}/u', '', $column);
            }

            $headers[] = strtoupper(trim($column));
        }

        return $headers;
    }

    /**
     * Nothing else than the errors and the number of imported rows is displayed by Thelia,
     * so the lines which have been left aside are reported here, with the totals allowing
     * to check that every line of the file has been accounted for.
     */
    protected function appendReport(?string $message): ?string
    {
        $report = [];

        if (null !== $message) {
            $report[] = $message;
        }

        foreach ($this->unreadableLineMessages as $unreadableLineMessage) {
            $report[] = $unreadableLineMessage;
        }

        $detailedWarnings = \array_slice($this->warnings, 0, self::MAX_DETAILED_WARNINGS);

        foreach ($detailedWarnings as $warning) {
            $report[] = $warning;
        }

        if (\count($this->warnings) > \count($detailedWarnings)) {
            $report[] = $this->trans(
                'And %count% other line(s) in the same case: those rules can be deleted from the rules page of the module.',
                ['%count%' => \count($this->warnings) - \count($detailedWarnings)]
            );
        }

        if (0 < $this->refusedLines + $this->unreadableLines + $this->completedLines + \count($this->warnings)) {
            $report[] = $this->trans(
                'Report: %total% data line(s) in the file, %imported% imported, %refused% refused, %unreadable% unreadable, %completed% completed with empty columns.',
                [
                    '%total%' => $this->dataLines > 0 ? $this->dataLines : \count($this->getData()),
                    '%imported%' => $this->importedRows,
                    '%refused%' => $this->refusedLines,
                    '%unreadable%' => $this->unreadableLines,
                    '%completed%' => $this->completedLines,
                ]
            );
        }

        return [] === $report ? null : implode('<br />', $report);
    }

    protected function error(string $message, array $parameters = []): string
    {
        return $this->prefixWithLineNumber($this->trans($message, $parameters));
    }

    protected function prefixWithLineNumber(string $message): string
    {
        return $this->trans(
            'Line %line%: %msg%',
            ['%line%' => $this->lineNumbers[$this->rowIndex - 1] ?? $this->rowIndex, '%msg%' => $message]
        );
    }

    protected function trans(string $message, array $parameters = []): string
    {
        return Translator::getInstance()->trans($message, $parameters, RewriteUrl::MODULE_DOMAIN);
    }
}
