<?php

namespace Tickets\Notifications\Policies;

use Tickets\Notifications\Channels\ManagerAlertChannel;
use Tickets\Notifications\Channels\UrgencyChannel;

class CriticalNotificationPolicy implements NotificationPolicy
{
    /**
     * @return array<int, string>
     */
    public function channels(): array
    {
        return ['mail', UrgencyChannel::class, ManagerAlertChannel::class];
    }
}
