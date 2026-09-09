<?php

namespace Tickets\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tickets\Database\Seeders\TicketAccessSeeder;
use Tickets\Enums\TicketRole;
use Tickets\Tests\TestCase;

/**
 * The three profiles are named on every screen, so their wording is part of
 * what the reader is promised in each locale.
 */
class TicketRoleWordingTest extends TestCase
{
    /**
     * @return array<string, array{TicketRole, string}>
     */
    public static function frenchLabels(): array
    {
        return [
            'requester' => [TicketRole::Requester, 'Demandeur'],
            'technician' => [TicketRole::Technician, 'Technicien'],
            'manager' => [TicketRole::Manager, 'Responsable'],
        ];
    }

    /**
     * @return array<string, array{TicketRole, string}>
     */
    public static function englishLabels(): array
    {
        return [
            'requester' => [TicketRole::Requester, 'Requester'],
            'technician' => [TicketRole::Technician, 'Technician'],
            'manager' => [TicketRole::Manager, 'Manager'],
        ];
    }

    #[DataProvider('frenchLabels')]
    public function test_each_role_reads_in_french(TicketRole $role, string $expected): void
    {
        $this->app->setLocale('fr');

        $this->assertSame($expected, __($role->translationKey()));
    }

    #[DataProvider('englishLabels')]
    public function test_each_role_still_reads_in_english(TicketRole $role, string $expected): void
    {
        $this->app->setLocale('en');

        $this->assertSame($expected, __($role->translationKey()));
    }

    public function test_the_reference_account_of_a_profile_carries_its_translated_name(): void
    {
        $this->app->setLocale('fr');

        $this->seed(TicketAccessSeeder::class);

        foreach (TicketRole::cases() as $role) {
            $this->assertDatabaseHas('users', [
                'email' => $role->referenceEmail(),
                'name' => __($role->translationKey()),
            ]);
        }
    }
}
