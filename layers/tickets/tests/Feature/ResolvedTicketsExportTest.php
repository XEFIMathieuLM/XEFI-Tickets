<?php

namespace Tickets\Tests\Feature;

use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class ResolvedTicketsExportTest extends TestCase
{
    private const EXPORT_URI = '/api/v1/tickets/exports/resolved-this-month';

    public function test_it_rejects_an_unauthenticated_export(): void
    {
        $this->getJson(self::EXPORT_URI)->assertUnauthorized();
    }

    public function test_it_exports_only_the_tickets_resolved_this_month(): void
    {
        Ticket::factory()->create([
            'title' => 'Resolved this month',
            'status' => TicketStatus::Resolved,
            'resolved_at' => now()->startOfMonth()->addHour(),
        ]);
        Ticket::factory()->create([
            'title' => 'Resolved long ago',
            'status' => TicketStatus::Resolved,
            'resolved_at' => now()->subMonths(3),
        ]);
        Ticket::factory()->create(['title' => 'Still open']);

        $response = $this->actingAs($this->userWith(TicketRole::Manager))->get(self::EXPORT_URI);

        $month = now()->format('Y-m');

        $response->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=utf-8')
            ->assertDownload("resolved-tickets-{$month}.csv");

        $csv = $response->streamedContent();

        $this->assertStringContainsString('id,title,status,priority,created_at,resolved_at', $csv);
        $this->assertStringContainsString('Resolved this month', $csv);
        $this->assertStringNotContainsString('Resolved long ago', $csv);
        $this->assertStringNotContainsString('Still open', $csv);
    }
}
