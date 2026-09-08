<?php

namespace Tickets\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Models\Attachment;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketRetentionTest extends TestCase
{
    public function test_it_purges_a_ticket_deleted_longer_ago_than_the_retention(): void
    {
        $expired = $this->softDeletedSince(Ticket::RETENTION_DAYS + 1);
        $recent = $this->softDeletedSince(1);

        $this->artisan('model:prune', ['--model' => [Ticket::class]])->assertSuccessful();

        $this->assertDatabaseMissing('tickets', ['id' => $expired->id]);
        $this->assertDatabaseHas('tickets', ['id' => $recent->id]);
    }

    public function test_it_never_purges_a_ticket_that_is_still_live(): void
    {
        $live = Ticket::factory()->create();

        $this->artisan('model:prune', ['--model' => [Ticket::class]])->assertSuccessful();

        $this->assertDatabaseHas('tickets', ['id' => $live->id]);
    }

    public function test_it_purges_a_ticket_that_carries_comments_and_attachments(): void
    {
        Storage::fake(AttachFileToTicket::DISK);

        $expired = $this->softDeletedSince(Ticket::RETENTION_DAYS + 1);
        Comment::factory()->count(2)->create(['ticket_id' => $expired->id]);
        $attachment = Attachment::factory()->stored()->create(['ticket_id' => $expired->id]);

        $this->artisan('model:prune', ['--model' => [Ticket::class]])->assertSuccessful();

        $this->assertDatabaseMissing('tickets', ['id' => $expired->id]);
        $this->assertDatabaseMissing('comments', ['ticket_id' => $expired->id]);
        $this->assertDatabaseMissing('ticket_attachments', ['ticket_id' => $expired->id]);
        Storage::disk(AttachFileToTicket::DISK)->assertMissing($attachment->path);
    }

    private function softDeletedSince(int $days): Ticket
    {
        $ticket = Ticket::factory()->create();
        $ticket->delete();
        $ticket->forceFill(['deleted_at' => now()->subDays($days)])->saveQuietly();

        return $ticket;
    }
}
