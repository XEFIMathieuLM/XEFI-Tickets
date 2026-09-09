<?php

namespace Tickets\Exceptions;

use RuntimeException;

/**
 * A technical failure, not a bad line: the import stops instead of pretending
 * the file held nothing.
 */
class ImportSourceUnreadable extends RuntimeException
{
    public static function at(string $path): self
    {
        return new self(sprintf('The import source "%s" could not be read.', $path));
    }
}
