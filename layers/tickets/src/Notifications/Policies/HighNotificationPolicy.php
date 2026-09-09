<?php

namespace Tickets\Notifications\Policies;

use Tickets\Notifications\Channels\UrgencyChannel;

class HighNotificationPolicy implements NotificationPolicy
{
    /**
     * @return array<int, string>
     */
    public function channels(): array
    {
        return ['mail', UrgencyChannel::class];
    }
}
