<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Testing\TestResponse;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketSearchApiTest extends TestCase
{
    private const SEARCH_URI = '/api/v1/tickets/search';

    public function test_it_rejects_an_unauthenticated_search(): void
    {
        $this->postJson(self::SEARCH_URI)->assertUnauthorized();
    }

    public function test_it_returns_a_paginated_page_of_tickets(): void
    {
        Ticket::factory()->count(15)->create();

        $this->searchTickets(['limit' => 10])
            ->assertOk()
            ->assertJsonPath('total', 15)
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('last_page', 2)
            ->assertJsonCount(10, 'data');
    }

    public function test_it_filters_on_a_given_status(): void
    {
        Ticket::factory()->count(3)->create(['status' => TicketStatus::Open]);
        Ticket::factory()->create(['status' => TicketStatus::Closed]);

        $response = $this->searchTickets([
            'filters' => [['field' => 'status', 'value' => TicketStatus::Closed->value]],
        ]);

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame(TicketStatus::Closed->value, $response->json('data.0.status'));
    }

    public function test_it_sorts_by_creation_date_descending(): void
    {
        $oldest = Ticket::factory()->create(['created_at' => now()->subWeek()]);
        $newest = Ticket::factory()->create(['created_at' => now()]);

        $this->searchTickets(['sorts' => [['field' => 'created_at', 'direction' => 'desc']]])
            ->assertOk()
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.1.id', $oldest->id);
    }

    public function test_it_loads_the_requester_with_the_ticket(): void
    {
        $requester = User::factory()->create(['name' => 'Ada Lovelace']);
        Ticket::factory()->create(['requester_id' => $requester->id]);

        $this->searchTickets(['includes' => [['relation' => 'requester']]])
            ->assertOk()
            ->assertJsonPath('data.0.requester.name', 'Ada Lovelace');
    }

    public function test_it_matches_the_title_textually(): void
    {
        Ticket::factory()->create(['title' => 'Printer jam on the second floor']);
        Ticket::factory()->create(['title' => 'VPN keeps dropping']);

        $response = $this->searchTickets([
            'filters' => [['field' => 'title', 'operator' => 'like', 'value' => '%printer%']],
        ]);

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('Printer jam on the second floor', $response->json('data.0.title'));
    }

    public function test_it_loads_the_comments_with_the_ticket(): void
    {
        $ticket = Ticket::factory()->create();
        Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'body' => 'Have you tried the other cable?',
        ]);

        $this->searchTickets(['includes' => [['relation' => 'comments']]])
            ->assertOk()
            ->assertJsonPath('data.0.comments.0.body', 'Have you tried the other cable?');
    }

    public function test_it_never_exposes_an_internal_column(): void
    {
        Ticket::factory()->create();

        $response = $this->searchTickets();

        $this->assertEqualsCanonicalizing(
            ['id', 'title', 'description', 'status', 'priority', 'created_at', 'resolved_at'],
            array_keys($response->json('data.0')),
        );
    }

    /**
     * Searches as a manager, the only profile whose perimeter spans every ticket.
     *
     * @param  array<string, mixed>  $search
     */
    private function searchTickets(array $search = []): TestResponse
    {
        return $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson(self::SEARCH_URI, ['search' => $search]);
    }
}
