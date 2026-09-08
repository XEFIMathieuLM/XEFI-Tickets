<?php

namespace Tickets\Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as ApplicationTestCase;
use Tickets\Database\Seeders\TicketAccessSeeder;
use Tickets\Enums\TicketRole;

abstract class TestCase extends ApplicationTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TicketAccessSeeder::class);
    }

    /**
     * A brand new user holding one of the three access profiles.
     */
    protected function userWith(TicketRole $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role->value);

        return $user;
    }
}
