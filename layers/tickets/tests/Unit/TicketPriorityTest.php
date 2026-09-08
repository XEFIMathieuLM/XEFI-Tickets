<?php

namespace Tickets\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tickets\Enums\TicketPriority;

class TicketPriorityTest extends TestCase
{
    /**
     * @return array<string, array{TicketPriority, int}>
     */
    public static function targets(): array
    {
        return [
            'low' => [TicketPriority::Low, 72],
            'normal' => [TicketPriority::Normal, 24],
            'high' => [TicketPriority::High, 8],
            'critical' => [TicketPriority::Critical, 2],
        ];
    }

    #[DataProvider('targets')]
    public function test_it_carries_the_target_of_each_priority(TicketPriority $priority, int $hours): void
    {
        $this->assertSame($hours, $priority->targetHandlingHours());
    }

    public function test_a_higher_priority_never_leaves_more_time(): void
    {
        $targets = array_map(
            fn (TicketPriority $priority): int => $priority->targetHandlingHours(),
            TicketPriority::cases(),
        );

        $descending = $targets;
        rsort($descending);

        $this->assertSame($descending, $targets);
    }
}
