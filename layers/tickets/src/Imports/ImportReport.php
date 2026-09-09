<?php

namespace Tickets\Imports;

/**
 * What the operator gets back: how many lines were read, created, rejected,
 * and why each rejection happened.
 */
class ImportReport
{
    /**
     * @param  array<int, string>  $rejections  keyed by source line number
     */
    public function __construct(
        public readonly int $read,
        public readonly int $created,
        public readonly array $rejections,
    ) {}

    public function rejected(): int
    {
        return count($this->rejections);
    }
}
