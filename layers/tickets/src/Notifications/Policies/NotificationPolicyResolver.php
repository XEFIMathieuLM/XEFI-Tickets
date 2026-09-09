<?php

namespace Tickets\Notifications\Policies;

use Illuminate\Support\Str;
use Tickets\Enums\TicketPriority;

/**
 * Resolves the policy by name from the priority. A new priority needs one new
 * class and nothing else; a priority with no class falls back to the standard.
 */
class NotificationPolicyResolver
{
    public function for(TicketPriority $priority): NotificationPolicy
    {
        $candidate = sprintf('%s\\%sNotificationPolicy', __NAMESPACE__, Str::studly($priority->value));

        return app(class_exists($candidate) ? $candidate : StandardNotificationPolicy::class);
    }
}
