<?php

namespace Tickets\Tests\Feature;

use Tickets\Database\Seeders\TicketsSeeder;
use Tickets\Enums\TicketPriority;
use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Models\Comment;
use Tickets\Tests\TestCase;

/**
 * The seeder is the development environment's contract: an environment where
 * three statuses out of five are missing hides three bugs out of five.
 */
class TicketsSeederTest extends TestCase
{
    public function test_it_covers_every_status_and_every_priority(): void
    {
        $this->seed(TicketsSeeder::class);

        foreach (TicketStatus::cases() as $status) {
            $this->assertDatabaseHas('tickets', ['status' => $status->value]);
        }

        foreach (TicketPriority::cases() as $priority) {
            $this->assertDatabaseHas('tickets', ['priority' => $priority->value]);
        }
    }

    public function test_it_leaves_comments_behind(): void
    {
        $this->seed(TicketsSeeder::class);

        $this->assertGreaterThan(0, Comment::count());
    }

    public function test_it_creates_one_reference_account_per_profile(): void
    {
        $this->seed(TicketsSeeder::class);

        foreach (TicketRole::cases() as $role) {
            $this->assertDatabaseHas('users', ['email' => $role->referenceEmail()]);
        }
    }
}
