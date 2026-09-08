<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Tickets\Models\Comment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class CommentTest extends TestCase
{
    public function test_it_belongs_to_its_ticket_and_its_author(): void
    {
        $ticket = Ticket::factory()->create();
        $author = User::factory()->create();

        $comment = Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'author_id' => $author->id,
        ]);

        $this->assertTrue($comment->ticket->is($ticket));
        $this->assertTrue($comment->author->is($author));
    }

    public function test_a_ticket_gathers_its_comments(): void
    {
        $ticket = Ticket::factory()->create();
        Comment::factory()->count(3)->create(['ticket_id' => $ticket->id]);
        Comment::factory()->create();

        $this->assertCount(3, $ticket->comments);
    }
}
