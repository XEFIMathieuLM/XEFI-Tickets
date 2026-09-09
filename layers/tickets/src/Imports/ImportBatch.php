<?php

namespace Tickets\Imports;

/**
 * The parcel every pipeline step receives and hands on: the surviving rows,
 * the rejections gathered so far, and what was finally written.
 */
class ImportBatch
{
    /**
     * @param  array<int, array<string, string>>  $rows  keyed by source line number
     * @param  array<int, string>  $rejections  keyed by source line number
     * @param  array<string, int>  $requesterIdsByEmail
     */
    public function __construct(
        public readonly string $contents,
        public array $rows = [],
        public array $rejections = [],
        public array $requesterIdsByEmail = [],
        public int $created = 0,
        public int $read = 0,
    ) {}

    public function reject(int $line, string $reason): void
    {
        unset($this->rows[$line]);

        $this->rejections[$line] = $reason;
    }

    /**
     * Rejections are gathered by several steps, so they are put back in line
     * order: the reader follows the file, not the pipeline.
     */
    public function report(): ImportReport
    {
        $ordered = $this->rejections;
        ksort($ordered);

        return new ImportReport($this->read, $this->created, $ordered);
    }
}
