<?php

namespace Tickets\Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Enums\TicketRole;
use Tickets\Livewire\TicketAttachments;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

class TicketAttachmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(AttachFileToTicket::DISK);
    }

    public function test_it_stores_the_file_and_records_where_it_went(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

        Livewire::actingAs($requester)
            ->test(TicketAttachments::class, ['ticket' => $ticket])
            ->set('upload', UploadedFile::fake()->create('quote.pdf', 120, 'application/pdf'))
            ->call('attach')
            ->assertHasNoErrors();

        $attachment = Attachment::sole();

        $this->assertSame('quote.pdf', $attachment->original_name);
        $this->assertSame($ticket->id, $attachment->ticket_id);
        $this->assertSame($requester->id, $attachment->uploaded_by_id);
        Storage::disk(AttachFileToTicket::DISK)->assertExists($attachment->path);
    }

    public function test_it_refuses_a_file_above_the_size_ceiling(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

        Livewire::actingAs($requester)
            ->test(TicketAttachments::class, ['ticket' => $ticket])
            ->set('upload', UploadedFile::fake()->create('huge.pdf', Attachment::MAX_SIZE_IN_KILOBYTES + 1))
            ->call('attach')
            ->assertHasErrors(['upload' => 'max']);

        $this->assertDatabaseCount('ticket_attachments', 0);
    }

    public function test_it_refuses_a_file_type_that_is_not_on_the_allow_list(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

        Livewire::actingAs($requester)
            ->test(TicketAttachments::class, ['ticket' => $ticket])
            ->set('upload', UploadedFile::fake()->create('payload.exe', 4))
            ->call('attach')
            ->assertHasErrors(['upload' => 'extensions']);

        $this->assertDatabaseCount('ticket_attachments', 0);
    }

    public function test_removing_an_attachment_takes_the_file_with_it(): void
    {
        $requester = $this->userWith(TicketRole::Requester);
        $ticket = Ticket::factory()->create(['requester_id' => $requester->id]);

        $attachment = app(AttachFileToTicket::class)->handle(
            $ticket, $requester, UploadedFile::fake()->create('note.txt', 2),
        );

        Livewire::actingAs($requester)
            ->test(TicketAttachments::class, ['ticket' => $ticket])
            ->call('remove', $attachment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('ticket_attachments', ['id' => $attachment->id]);
        Storage::disk(AttachFileToTicket::DISK)->assertMissing($attachment->path);
    }

    public function test_a_user_outside_the_perimeter_cannot_open_the_attachments(): void
    {
        $ticket = Ticket::factory()->create();

        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketAttachments::class, ['ticket' => $ticket])
            ->assertForbidden();
    }

    public function test_the_attachments_are_read_in_a_single_statement(): void
    {
        $ticket = Ticket::factory()->create();
        Attachment::factory()->count(10)->create(['ticket_id' => $ticket->id]);

        $statements = $this->sqlOnAttachmentSearch($this->userWith(TicketRole::Manager));

        $touchingAttachments = array_filter(
            $statements,
            fn (string $sql): bool => str_contains($sql, 'ticket_attachments'),
        );

        $this->assertCount(1, $touchingAttachments);
    }

    public function test_the_api_exposes_the_attachments_without_saying_where_they_live(): void
    {
        $ticket = Ticket::factory()->create();
        Attachment::factory()->create(['ticket_id' => $ticket->id, 'original_name' => 'screenshot.png']);

        $response = $this->actingAs($this->userWith(TicketRole::Manager))
            ->postJson('/api/v1/tickets/search', [
                'search' => ['includes' => [['relation' => 'attachments']]],
            ]);

        $response->assertOk()->assertJsonPath('data.0.attachments.0.original_name', 'screenshot.png');

        $this->assertEqualsCanonicalizing(
            ['id', 'original_name', 'mime_type', 'size_in_bytes', 'created_at'],
            array_keys($response->json('data.0.attachments.0')),
        );
    }

    /**
     * @return array<int, string>
     */
    private function sqlOnAttachmentSearch(User $actor): array
    {
        $statements = [];
        DB::listen(function ($query) use (&$statements): void {
            $statements[] = $query->sql;
        });

        $this->actingAs($actor)
            ->postJson('/api/v1/tickets/search', [
                'search' => ['includes' => [['relation' => 'attachments']]],
            ])
            ->assertOk();

        return $statements;
    }
}
