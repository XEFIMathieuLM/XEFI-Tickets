<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Tickets\Enums\TicketStatus;
use Tickets\Imports\ImportBatch;
use Tickets\Imports\Steps\CreateTickets;
use Tickets\Imports\Steps\ResolveRequesters;
use Tickets\Imports\Steps\ValidateRows;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

/**
 * The three steps that need the validator or the database, each on its own.
 */
class ImportWritingStepsTest extends TestCase
{
    public function test_validation_drops_the_bad_row_and_keeps_the_good_one(): void
    {
        $batch = $this->pass(new ValidateRows, new ImportBatch('', [
            2 => $this->row(),
            3 => ['title' => '', 'description' => 'Body', 'priority' => 'low', 'requester_email' => 'ada@xefi.test'],
        ]));

        $this->assertSame([2], array_keys($batch->rows));
        $this->assertSame([3], array_keys($batch->rejections));
    }

    public function test_validation_refuses_a_priority_the_enum_does_not_know(): void
    {
        $batch = $this->pass(new ValidateRows, new ImportBatch('', [
            2 => ['title' => 'T', 'description' => 'B', 'priority' => 'urgentissime', 'requester_email' => 'a@b.test'],
        ]));

        $this->assertSame([2], array_keys($batch->rejections));
    }

    public function test_resolving_finds_every_requester_in_a_single_query(): void
    {
        $ada = User::factory()->create(['email' => 'ada@xefi.test']);
        $rows = [];

        for ($line = 2; $line <= 21; $line++) {
            $rows[$line] = $this->row();
        }

        $batch = $this->pass(new ResolveRequesters, new ImportBatch('', $rows));

        $this->assertSame([$ada->email => $ada->id], $batch->requesterIdsByEmail);
        $this->assertCount(20, $batch->rows);
    }

    public function test_resolving_rejects_a_row_whose_requester_is_unknown(): void
    {
        $batch = $this->pass(new ResolveRequesters, new ImportBatch('', [
            2 => $this->row(),
        ]));

        $this->assertSame([], $batch->rows);
        $this->assertStringContainsString('ada@xefi.test', $batch->rejections[2]);
    }

    public function test_creation_writes_the_surviving_rows_as_open_tickets(): void
    {
        $ada = User::factory()->create(['email' => 'ada@xefi.test']);

        $batch = $this->pass(new CreateTickets, new ImportBatch(
            contents: '',
            rows: [2 => $this->row()],
            requesterIdsByEmail: [$ada->email => $ada->id],
        ));

        $this->assertSame(1, $batch->created);
        $this->assertDatabaseHas('tickets', [
            'title' => 'Printer down',
            'requester_id' => $ada->id,
            'status' => TicketStatus::Open->value,
        ]);
    }

    public function test_creation_writes_nothing_when_every_row_was_rejected(): void
    {
        $batch = $this->pass(new CreateTickets, new ImportBatch(''));

        $this->assertSame(0, $batch->created);
        $this->assertSame(0, Ticket::count());
    }

    /**
     * @return array<string, string>
     */
    private function row(): array
    {
        return [
            'title' => 'Printer down',
            'description' => 'Nothing prints',
            'priority' => 'low',
            'requester_email' => 'ada@xefi.test',
        ];
    }

    private function pass(object $step, ImportBatch $batch): ImportBatch
    {
        return $step->handle($batch, fn (ImportBatch $passed): ImportBatch => $passed);
    }
}
