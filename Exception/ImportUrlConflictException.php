<?php

namespace RewriteUrl\Exception;

/**
 * Thrown when an imported line cannot be applied as is, because it would conflict
 * with URLs already rewritten by Thelia (and break them).
 *
 * The message is already translated, and is displayed as is in the import report.
 */
class ImportUrlConflictException extends \RuntimeException
{
}
