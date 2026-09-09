<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tickets\Enums\TicketPriority;
use Tickets\Models\Ticket;
use Tickets\Notifications\Channels\ManagerAlertChannel;
use Tickets\Notifications\Channels\UrgencyChannel;
use Tickets\Notifications\Policies\NotificationPolicyResolver;
use Tickets\Notifications\Policies\StandardNotificationPolicy;
use Tickets\Notifications\TicketAssignedNotification;
use Tickets\Tests\TestCase;

class NotificationChannelsTest extends TestCase
{
    /**
     * @return array<string, array{TicketPriority, array<int, string>}>
     */
    public static function priorities(): array
    {
        return [
            'low speaks by mail' => [TicketPriority::Low, ['mail']],
            'normal speaks by mail' => [TicketPriority::Normal, ['mail']],
            'high adds urgency' => [TicketPriority::High, ['mail', UrgencyChannel::class]],
            'critical alerts as well' => [
                TicketPriority::Critical,
                ['mail', UrgencyChannel::class, ManagerAlertChannel::class],
            ],
        ];
    }

    /**
     * @param  array<int, string>  $expected
     */
    #[DataProvider('priorities')]
    public function test_a_priority_produces_its_channels(TicketPriority $priority, array $expected): void
    {
        $ticket = Ticket::factory()->create(['priority' => $priority]);

        $channels = (new TicketAssignedNotification($ticket))->via(User::factory()->create());

        $this->assertSame($expected, $channels);
    }

    public function test_a_priority_with_no_policy_of_its_own_falls_back_to_the_standard(): void
    {
        $resolver = app(NotificationPolicyResolver::class);

        $this->assertInstanceOf(
            StandardNotificationPolicy::class,
            $resolver->for(TicketPriority::Low),
        );
    }
}
