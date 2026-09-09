<?php

namespace Tickets\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Livewire\TicketList;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketListTest extends TestCase
{
    public function test_it_paginates_at_twenty_five(): void
    {
        Ticket::factory()->count(30)->create();

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->count() === TicketList::PER_PAGE
                && $tickets->total() === 30);
    }

    public function test_the_pagination_speaks_french(): void
    {
        $this->app->setLocale('fr');
        Ticket::factory()->count(30)->create();

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Affichage de')
            ->assertSee('résultats')
            ->assertDontSee('Showing');
    }

    public function test_a_closed_ticket_leaves_the_list_for_the_archive(): void
    {
        Ticket::factory()->count(3)->create(['status' => TicketStatus::Open]);
        Ticket::factory()->count(2)->create(['status' => TicketStatus::Closed]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->total() === 3);
    }

    public function test_a_ticket_closed_under_the_reader_disappears_from_the_list(): void
    {
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Open,
            'title' => 'Printer replaced at last',
        ]);

        $screen = Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->assertSee('Printer replaced at last');

        $ticket->forceFill(['status' => TicketStatus::Closed])->save();

        $screen->call('$refresh')->assertDontSee('Printer replaced at last');
    }

    public function test_the_filter_never_offers_a_status_the_list_does_not_hold(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->assertViewHas('statuses', fn (array $statuses): bool => ! in_array(TicketStatus::Closed, $statuses, true))
            ->assertDontSee(__(TicketStatus::Closed->translationKey()));
    }

    public function test_the_list_is_called_mine_to_whoever_only_reaches_its_own(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        Ticket::factory()->create(['requester_id' => $requester->id]);

        Livewire::actingAs($requester)
            ->test(TicketList::class)
            ->assertSee(__('tickets::list.heading_own'))
            ->assertDontSee(__('tickets::list.columns.requester'));
    }

    public function test_the_list_keeps_its_plain_name_for_the_support_team(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->assertSee(__('tickets::list.heading'))
            ->assertDontSee(__('tickets::list.heading_own'));
    }

    public function test_the_page_renders_for_a_signed_in_user(): void
    {
        Ticket::factory()->create(['title' => 'Screen stays black']);

        $this->actingAs($this->userWith(TicketRole::Manager))
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Screen stays black');
    }

    public function test_it_shows_a_requester_only_its_own_tickets(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        Ticket::factory()->count(2)->create(['requester_id' => $requester->id]);
        Ticket::factory()->count(4)->create();

        Livewire::actingAs($requester)
            ->test(TicketList::class)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->total() === 2);
    }

    public function test_filtering_sends_the_reader_back_to_the_first_page(): void
    {
        Ticket::factory()->count(30)->create(['status' => TicketStatus::Open]);
        Ticket::factory()->create(['status' => TicketStatus::InProgress]);

        $component = Livewire::actingAs($this->userWith(TicketRole::Manager))->test(TicketList::class);

        $component->call('gotoPage', 2);
        $component->assertViewHas('tickets', fn ($tickets): bool => $tickets->currentPage() === 2);

        $component->set('status', TicketStatus::InProgress->value);
        $component->assertViewHas('tickets', fn ($tickets): bool => $tickets->currentPage() === 1
            && $tickets->total() === 1);
    }

    public function test_it_filters_on_a_priority(): void
    {
        Ticket::factory()->count(3)->create(['priority' => TicketPriority::Low]);
        Ticket::factory()->create(['priority' => TicketPriority::Critical]);

        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->set('priority', TicketPriority::Critical->value)
            ->assertViewHas('tickets', fn ($tickets): bool => $tickets->total() === 1);
    }

    public function test_clicking_the_same_column_twice_reverses_the_sort(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->call('sortBy', 'title')
            ->assertSet('sortColumn', 'title')
            ->assertSet('sortDirection', 'asc')
            ->call('sortBy', 'title')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_a_column_outside_the_allow_list_never_reaches_the_query(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Manager))
            ->test(TicketList::class)
            ->call('sortBy', 'requester_id')
            ->assertSet('sortColumn', 'created_at')
            ->assertSet('sortDirection', 'desc');
    }

    public function test_the_query_count_does_not_follow_the_number_of_rows(): void
    {
        $viewer = $this->userWith(TicketRole::Manager);
        $tickets = Ticket::factory()->count(10)->create();
        $tickets->each(fn (Ticket $ticket) => Comment::factory()->count(2)->create(['ticket_id' => $ticket->id]));

        $statements = [];
        DB::listen(function ($query) use (&$statements): void {
            $statements[] = $query->sql;
        });

        Livewire::actingAs($viewer)->test(TicketList::class)->assertOk();

        $touchingComments = array_filter(
            $statements,
            fn (string $sql): bool => str_starts_with($sql, 'select * from `comments`'),
        );
        $touchingUsers = array_filter(
            $statements,
            fn (string $sql): bool => str_contains($sql, 'from `users`'),
        );

        $this->assertCount(0, $touchingComments);
        $this->assertLessThanOrEqual(2, count($touchingUsers));
    }
}
