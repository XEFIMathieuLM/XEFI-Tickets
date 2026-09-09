<?php

namespace Tickets\Notifications\Policies;

/**
 * The quiet default: a mail, and nothing else.
 */
class StandardNotificationPolicy implements NotificationPolicy
{
    /**
     * @return array<int, string>
     */
    public function channels(): array
    {
        return ['mail'];
    }
}
