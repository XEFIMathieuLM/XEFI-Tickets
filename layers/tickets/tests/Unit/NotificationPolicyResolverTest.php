<?php

namespace Tickets\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tickets\Notifications\Channels\ManagerAlertChannel;
use Tickets\Notifications\Channels\UrgencyChannel;
use Tickets\Notifications\Policies\CriticalNotificationPolicy;
use Tickets\Notifications\Policies\HighNotificationPolicy;
use Tickets\Notifications\Policies\StandardNotificationPolicy;

/**
 * The policies themselves need neither database nor container.
 */
class NotificationPolicyResolverTest extends TestCase
{
    /**
     * @return array<string, array{object, array<int, string>}>
     */
    public static function policies(): array
    {
        return [
            'standard' => [new StandardNotificationPolicy, ['mail']],
            'high' => [new HighNotificationPolicy, ['mail', UrgencyChannel::class]],
            'critical' => [
                new CriticalNotificationPolicy,
                ['mail', UrgencyChannel::class, ManagerAlertChannel::class],
            ],
        ];
    }

    /**
     * @param  array<int, string>  $expected
     */
    #[DataProvider('policies')]
    public function test_each_policy_names_its_channels(object $policy, array $expected): void
    {
        $this->assertSame($expected, $policy->channels());
    }
}
