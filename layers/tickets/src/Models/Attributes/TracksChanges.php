<?php

namespace Tickets\Models\Attributes;

use Attribute;

/**
 * Declares, on the model itself, which columns are worth a journal entry.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class TracksChanges
{
    /**
     * @param  array<int, string>  $columns
     */
    public function __construct(public readonly array $columns) {}
}
