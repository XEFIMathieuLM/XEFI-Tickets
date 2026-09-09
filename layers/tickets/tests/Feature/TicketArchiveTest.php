<?php

namespace Tickets\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Livewire\TicketArchive;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

/**
 * The archive gathers what the support team has finished with. It reads the
 * same perimeters as the live list, so nobody sees more here than there.
 */
class TicketArchiveTest extends TestCase
{
    public function test_it_gathers_the_closed_tickets_and_nothing_else(): void
    {
        Ticket::factory()->count(3)->create(['status' => TicketStatus::Closed]);
        Ticket::factory()->count(4)->create(['status' => TicketStatus::Open]);
        Ticket::factory()->create(['status' => TicketStatus::Resolved]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->total() === 3);
    }

    public function test_the_page_renders_for_a_signed_in_user(): void
    {
        Ticket::factory()->create([
            'status' => TicketStatus::Closed,
            'title' => 'Printer replaced at last',
        ]);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->get(route('tickets.archive'))
            ->assertOk()
            ->assertSee('Printer replaced at last');
    }

    public function test_it_sends_a_guest_to_the_sign_in_screen(): void
    {
        $this->get(route('tickets.archive'))->assertRedirect(route('login'));
    }

    public function test_a_requester_only_finds_the_tickets_it_opened(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        Ticket::factory()->count(2)->create([
            'requester_id' => $requester->id,
            'status' => TicketStatus::Closed,
        ]);
        Ticket::factory()->count(5)->create(['status' => TicketStatus::Closed]);

        Livewire::actingAs($requester)
            ->test(TicketArchive::class)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->total() === 2);
    }

    public function test_a_technician_only_finds_the_tickets_it_handled(): void
    {
        $technician = $this->userWith(TicketRole::Technician);
        Ticket::factory()->count(3)->create([
            'assigned_technician_id' => $technician->id,
            'status' => TicketStatus::Closed,
        ]);
        Ticket::factory()->count(2)->create(['status' => TicketStatus::Closed]);

        Livewire::actingAs($technician)
            ->test(TicketArchive::class)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->total() === 3);
    }

    public function test_it_paginates_at_twenty_five(): void
    {
        Ticket::factory()->count(30)->create(['status' => TicketStatus::Closed]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->count() === TicketArchive::PER_PAGE
                && $tickets->total() === 30);
    }

    public function test_a_column_outside_the_allow_list_never_reaches_the_query(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->call('sortBy', 'assigned_technician_id')
            ->assertSet('sortColumn', 'created_at')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_clicking_the_same_column_twice_reverses_the_sort(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->call('sortBy', 'resolved_at')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'resolved_at')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_the_requester_column_is_hidden_from_whoever_sees_only_its_own(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        Ticket::factory()->create([
            'requester_id' => $requester->id,
            'status' => TicketStatus::Closed,
        ]);

        Livewire::actingAs($requester)
            ->test(TicketArchive::class)
            ->assertDontSee(__('tickets::archive.columns.requester'));

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->assertSee(__('tickets::archive.columns.requester'));
    }

    public function test_the_query_count_does_not_follow_the_number_of_rows(): void
    {
        $tickets = Ticket::factory()->count(10)->create(['status' => TicketStatus::Closed]);
        $tickets->each(fn (Ticket $ticket) => Comment::factory()->count(2)->create(['ticket_id' => $ticket->id]));

        $statements = [];
        DB::listen(function ($query) use (&$statements): void {
            $statements[] = $query->sql;
        });

        Livewire::actingAs($this->userWith(TicketRole::Manager))->test(TicketArchive::class)->assertOk();

        $touchingUsers = array_filter(
            $statements,
            fn (string $sql): bool => str_starts_with($sql, 'select * from `users`'),
        );

        $this->assertLessThanOrEqual(2, count($touchingUsers));
    }

    public function test_it_tells_the_support_team_whether_the_target_was_met(): void
    {
        Ticket::factory()->create([
            'status' => TicketStatus::Closed,
            'resolved_at' => now(),
            'is_resolved_on_time' => true,
        ]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->assertSee(__('tickets::archive.columns.on_time'))
            ->assertSee(__('tickets::archive.on_time.met'));
    }

    /**
     * How the support team fares against its own targets is its business, and
     * the requester never chose the importance the target is drawn from.
     */
    public function test_the_target_is_kept_from_whoever_only_follows_its_own_requests(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        Ticket::factory()->create([
            'requester_id' => $requester->id,
            'status' => TicketStatus::Closed,
            'resolved_at' => now(),
            'is_resolved_on_time' => false,
        ]);

        Livewire::actingAs($requester)
            ->test(TicketArchive::class)
            ->assertDontSee(__('tickets::archive.columns.on_time'))
            ->assertDontSee(__('tickets::archive.on_time.missed'));
    }

    public function test_it_says_so_when_a_ticket_was_closed_without_being_resolved(): void
    {
        Ticket::factory()->create([
            'status' => TicketStatus::Closed,
            'resolved_at' => null,
        ]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->assertSee(__('tickets::archive.never_resolved'));
    }

    public function test_it_says_so_when_nothing_has_been_closed_yet(): void
    {
        Ticket::factory()->count(3)->create(['status' => TicketStatus::Open]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketArchive::class)
            ->assertSee(__('tickets::archive.empty'));
    }
}
