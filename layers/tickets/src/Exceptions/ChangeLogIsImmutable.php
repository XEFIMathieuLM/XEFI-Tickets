<?php

namespace Tickets\Exceptions;

use RuntimeException;

/**
 * A journal entry records what happened; rewriting it would defeat its purpose.
 */
class ChangeLogIsImmutable extends RuntimeException
{
    public static function create(): self
    {
        return new self('A change log entry cannot be modified once written.');
    }
}
