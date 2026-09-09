<?php

namespace Tickets\Tests\Feature;

use Tickets\Enums\TicketRole;
use Tickets\Enums\TicketStatus;
use Tickets\Exceptions\ChangeLogIsImmutable;
use Tickets\Models\ChangeLog;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class RecordsChangesTest extends TestCase
{
    public function test_changing_a_tracked_column_writes_both_values(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $ticket->status = TicketStatus::InProgress;
        $ticket->save();

        $this->assertDatabaseHas('ticket_change_logs', [
            'loggable_type' => $ticket->getMorphClass(),
            'loggable_id' => $ticket->id,
            'attribute' => 'status',
            'old_value' => TicketStatus::Open->value,
            'new_value' => TicketStatus::InProgress->value,
        ]);
    }

    public function test_changing_an_untracked_column_writes_nothing(): void
    {
        $ticket = Ticket::factory()->create();

        $ticket->title = 'A clearer title';
        $ticket->save();

        $this->assertDatabaseCount('ticket_change_logs', 0);
    }

    public function test_it_records_the_signed_in_author(): void
    {
        $author = $this->userWith(TicketRole::Manager);
        $this->actingAs($author);

        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
        $ticket->status = TicketStatus::InProgress;
        $ticket->save();

        $this->assertSame($author->id, ChangeLog::sole()->author_id);
    }

    public function test_it_records_a_change_made_with_nobody_signed_in(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);

        $ticket->status = TicketStatus::InProgress;
        $ticket->save();

        $this->assertNull(ChangeLog::sole()->author_id);
    }

    public function test_the_same_trait_journals_a_comment_without_a_line_of_its_own(): void
    {
        $comment = Comment::factory()->create(['body' => 'First reading']);

        $comment->body = 'Second reading';
        $comment->save();

        $this->assertDatabaseHas('ticket_change_logs', [
            'loggable_type' => $comment->getMorphClass(),
            'loggable_id' => $comment->id,
            'attribute' => 'body',
            'old_value' => 'First reading',
            'new_value' => 'Second reading',
        ]);
    }

    public function test_a_journal_entry_can_never_be_rewritten(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
        $ticket->status = TicketStatus::InProgress;
        $ticket->save();

        $entry = ChangeLog::sole();

        $this->assertThrows(
            function () use ($entry): void {
                $entry->attribute = 'tampered';
                $entry->save();
            },
            ChangeLogIsImmutable::class,
        );
    }

    public function test_the_journal_reaches_back_to_its_subject_and_its_author(): void
    {
        $author = $this->userWith(TicketRole::Manager);
        $this->actingAs($author);

        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
        $ticket->status = TicketStatus::InProgress;
        $ticket->save();

        $entry = ChangeLog::sole();

        $this->assertTrue($entry->loggable->is($ticket));
        $this->assertTrue($entry->author->is($author));
        $this->assertCount(1, $ticket->changeLogs);
    }

    public function test_it_purges_entries_older_than_the_retention(): void
    {
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Open]);
        $ticket->status = TicketStatus::InProgress;
        $ticket->save();

        $stale = ChangeLog::sole();
        $stale->forceFill(['created_at' => now()->subDays(ChangeLog::RETENTION_DAYS + 1)])->saveQuietly();

        $this->artisan('model:prune', ['--model' => [ChangeLog::class]])->assertSuccessful();

        $this->assertDatabaseCount('ticket_change_logs', 0);
    }
}
