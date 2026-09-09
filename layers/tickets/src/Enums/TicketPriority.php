<?php

namespace Tickets\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Critical = 'critical';

    /**
     * Where a ticket sits before the support team has weighed it.
     */
    public static function default(): self
    {
        return self::Normal;
    }

    /**
     * The key a view translates. The wording itself never enters the enum.
     */
    public function translationKey(): string
    {
        return "tickets::priority.{$this->value}";
    }

    /**
     * The step up an escalation takes. Nothing climbs above Critical.
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Low => self::Normal,
            self::Normal => self::High,
            self::High => self::Critical,
            self::Critical => null,
        };
    }

    /**
     * Target handling time, in hours, before the ticket must be dealt with.
     */
    public function targetHandlingHours(): int
    {
        return match ($this) {
            self::Low => 72,
            self::Normal => 24,
            self::High => 8,
            self::Critical => 2,
        };
    }
}
