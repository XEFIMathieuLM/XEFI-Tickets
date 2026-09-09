<?php

namespace Tickets\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tickets\Imports\ImportBatch;
use Tickets\Imports\Steps\NormaliseRows;
use Tickets\Imports\Steps\ReadRows;

/**
 * The two steps that only shape text: no database, no container.
 */
class ImportReadingStepsTest extends TestCase
{
    public function test_reading_keys_every_row_by_its_source_line(): void
    {
        $batch = $this->pass(new ReadRows, new ImportBatch(implode("\n", [
            'title,description,priority,requester_email',
            'First,Body,low,ada@xefi.test',
            'Second,Body,high,ada@xefi.test',
        ])));

        $this->assertSame(2, $batch->read);
        $this->assertSame([2, 3], array_keys($batch->rows));
        $this->assertSame('First', $batch->rows[2]['title']);
    }

    public function test_reading_tolerates_columns_in_another_order(): void
    {
        $batch = $this->pass(new ReadRows, new ImportBatch(implode("\n", [
            'requester_email,priority,title,description',
            'ada@xefi.test,low,Reordered,Body',
        ])));

        $this->assertSame('Reordered', $batch->rows[2]['title']);
        $this->assertSame('ada@xefi.test', $batch->rows[2]['requester_email']);
    }

    public function test_reading_leaves_a_missing_column_empty(): void
    {
        $batch = $this->pass(new ReadRows, new ImportBatch(implode("\n", [
            'title,description',
            'Half a row,Body',
        ])));

        $this->assertSame('', $batch->rows[2]['priority']);
    }

    public function test_normalising_squeezes_spacing_and_lowers_the_lookup_columns(): void
    {
        $batch = new ImportBatch('', [
            2 => [
                'title' => "  Spaced   out\t",
                'description' => ' Body   here ',
                'priority' => ' HIGH ',
                'requester_email' => ' Ada@XEFI.test ',
            ],
        ]);

        $normalised = $this->pass(new NormaliseRows, $batch)->rows[2];

        $this->assertSame('Spaced out', $normalised['title']);
        $this->assertSame('Body here', $normalised['description']);
        $this->assertSame('high', $normalised['priority']);
        $this->assertSame('ada@xefi.test', $normalised['requester_email']);
    }

    private function pass(object $step, ImportBatch $batch): ImportBatch
    {
        return $step->handle($batch, fn (ImportBatch $passed): ImportBatch => $passed);
    }
}
