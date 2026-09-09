<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketStatus;
use Tickets\Exceptions\ImportSourceUnreadable;
use Tickets\Imports\ImportReport;
use Tickets\Jobs\ImportTicketsFromCsv;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class ImportTicketsFromCsvTest extends TestCase
{
    private const HEADER = 'title,description,priority,requester_email';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_a_clean_file_creates_every_ticket(): void
    {
        $requester = User::factory()->create(['email' => 'ada@xefi.test']);

        $report = $this->import([
            'Printer down,Nothing prints,high,ada@xefi.test',
            'VPN drops,Every ten minutes,low,ada@xefi.test',
        ]);

        $this->assertSame(2, $report->read);
        $this->assertSame(2, $report->created);
        $this->assertSame(0, $report->rejected());
        $this->assertDatabaseHas('tickets', [
            'title' => 'Printer down',
            'requester_id' => $requester->id,
            'status' => TicketStatus::Open->value,
            'priority' => TicketPriority::High->value,
        ]);
    }

    public function test_invalid_lines_are_reported_with_their_number_and_the_rest_is_created(): void
    {
        User::factory()->create(['email' => 'ada@xefi.test']);

        $report = $this->import([
            'Good one,Fine,low,ada@xefi.test',
            ',Missing title,low,ada@xefi.test',
            'Bad priority,Fine,urgentissime,ada@xefi.test',
            'Bad email,Fine,low,not-an-email',
            'Unknown account,Fine,low,ghost@xefi.test',
            'Missing description,,low,ada@xefi.test',
            'Another good one,Fine,critical,ada@xefi.test',
        ]);

        $this->assertSame(7, $report->read);
        $this->assertSame(2, $report->created);
        $this->assertSame(5, $report->rejected());
        $this->assertSame([3, 4, 5, 6, 7], array_keys($report->rejections));
    }

    public function test_an_unknown_requester_is_named_in_the_rejection(): void
    {
        $report = $this->import(['Orphan,Fine,low,ghost@xefi.test']);

        $this->assertStringContainsString('ghost@xefi.test', $report->rejections[2]);
    }

    public function test_a_missing_source_stops_the_import(): void
    {
        $this->assertThrows(
            fn () => (new ImportTicketsFromCsv('imports/absent.csv'))->run(),
            ImportSourceUnreadable::class,
        );
    }

    public function test_the_query_count_does_not_grow_with_the_number_of_lines(): void
    {
        User::factory()->create(['email' => 'ada@xefi.test']);

        $forTwo = $this->countQueriesImporting(2);
        $forTwoHundred = $this->countQueriesImporting(200);

        $this->assertSame($forTwo, $forTwoHundred);
        $this->assertSame(202, Ticket::count());
    }

    private function countQueriesImporting(int $lines): int
    {
        $rows = [];

        for ($position = 1; $position <= $lines; $position++) {
            $rows[] = sprintf('Ticket %d,Description,low,ada@xefi.test', $position);
        }

        $executed = 0;
        DB::listen(function () use (&$executed): void {
            $executed++;
        });

        $this->import($rows);

        return $executed;
    }

    /**
     * @param  array<int, string>  $rows
     */
    private function import(array $rows): ImportReport
    {
        $path = sprintf('imports/%s.csv', uniqid());

        Storage::disk('local')->put($path, implode("\n", array_merge([self::HEADER], $rows)));

        return (new ImportTicketsFromCsv($path))->run();
    }
}
