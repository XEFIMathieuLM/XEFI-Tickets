<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Tickets\Access\Controls\TicketControl;
use Tickets\Enums\TicketPermission;
use Tickets\Enums\TicketRole;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketScopingTest extends TestCase
{
    private const SEARCH_URI = '/api/v1/tickets/search';

    public function test_a_requester_may_only_act_on_the_tickets_it_opened(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $own = Ticket::factory()->create(['requester_id' => $requester->id]);
        $somebodyElse = Ticket::factory()->create();

        $this->assertTrue($requester->can('update', $own));
        $this->assertFalse($requester->can('update', $somebodyElse));
        $this->assertFalse($requester->can('view', $somebodyElse));
        $this->assertFalse($requester->can('delete', $own));
    }

    /**
     * The first perimeter is not an overlay: were it to rest on an acting
     * permission alone, whoever holds one would reach every ticket.
     */
    public function test_holding_the_closing_permission_never_opens_the_whole_base(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $technician = $this->userWith(TicketRole::Technician);
        $somebodyElse = Ticket::factory()->create();

        $this->assertTrue($requester->can(TicketPermission::Close->value));
        $this->assertTrue($technician->can(TicketPermission::Close->value));

        $this->assertFalse($requester->can('update', $somebodyElse));
        $this->assertFalse($technician->can('update', $somebodyElse));
    }

    public function test_a_requester_only_retrieves_the_tickets_it_opened(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        Ticket::factory()->count(2)->create(['requester_id' => $requester->id]);
        Ticket::factory()->count(5)->create();

        $this->actingAs($requester)->postJson(self::SEARCH_URI)
            ->assertOk()
            ->assertJsonPath('total', 2);
    }

    public function test_a_technician_only_retrieves_the_tickets_assigned_to_it(): void
    {
        $technician = $this->userWith(TicketRole::Technician);
        Ticket::factory()->count(3)->assigned()->create(['assigned_technician_id' => $technician->id]);
        Ticket::factory()->count(5)->create();

        $this->actingAs($technician)->postJson(self::SEARCH_URI)
            ->assertOk()
            ->assertJsonPath('total', 3);
    }

    public function test_a_manager_retrieves_every_ticket(): void
    {
        Ticket::factory()->count(7)->create();

        $this->actingAs($this->userWith(TicketRole::Manager))->postJson(self::SEARCH_URI)
            ->assertOk()
            ->assertJsonPath('total', 7);
    }

    public function test_a_user_without_any_permission_retrieves_nothing(): void
    {
        Ticket::factory()->count(4)->create();

        $this->actingAs(User::factory()->create())->postJson(self::SEARCH_URI)
            ->assertForbidden();
    }

    public function test_the_restriction_is_carried_by_the_sql_not_applied_afterwards(): void
    {
        $this->actingAs($requester = $this->userWith(TicketRole::Requester));

        $query = app(TicketControl::class)->forCurrentUser(Ticket::query());

        $this->assertStringContainsString('"requester_id" = ?', str_replace('`', '"', $query->toSql()));
        $this->assertContains($requester->id, $query->getBindings());
    }

    public function test_a_manager_query_carries_no_restriction(): void
    {
        $this->actingAs($this->userWith(TicketRole::Manager));

        $sql = str_replace('`', '"', app(TicketControl::class)->forCurrentUser(Ticket::query())->toSql());

        $this->assertStringNotContainsString('requester_id', $sql);
        $this->assertStringNotContainsString('assigned_technician_id', $sql);
        $this->assertStringNotContainsString('0=1', $sql);
    }
}
