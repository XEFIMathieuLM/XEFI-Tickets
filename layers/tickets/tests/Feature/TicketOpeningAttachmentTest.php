<?php

namespace Tickets\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Enums\TicketRole;
use Tickets\Livewire\TicketForm;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;
use Tickets\Tests\TestCase;

/**
 * A requester describes its problem and hands over the evidence in one go,
 * rather than opening the ticket first and coming back for the file.
 */
class TicketOpeningAttachmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(AttachFileToTicket::DISK);
    }

    public function test_a_requester_joins_a_file_while_opening_the_ticket(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        Livewire::actingAs($requester)
            ->test(TicketForm::class)
            ->set('title', 'The printer jams')
            ->set('description', 'Paper gets stuck every other page.')
            ->set('upload', UploadedFile::fake()->create('trace.log', 12))
            ->call('save')
            ->assertHasNoErrors();

        $ticket = Ticket::sole();
        $attachment = Attachment::sole();

        $this->assertSame($ticket->id, $attachment->ticket_id);
        $this->assertSame($requester->id, $attachment->uploaded_by_id);
        $this->assertSame('trace.log', $attachment->original_name);
        Storage::disk(AttachFileToTicket::DISK)->assertExists($attachment->path);
    }

    public function test_opening_a_ticket_without_a_file_stays_possible(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketForm::class)
            ->set('title', 'No evidence to hand')
            ->set('description', 'Nothing to attach.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('tickets', 1);
        $this->assertDatabaseCount('ticket_attachments', 0);
    }

    public function test_a_file_above_the_ceiling_opens_no_ticket_at_all(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketForm::class)
            ->set('title', 'Far too heavy')
            ->set('description', 'The file will be refused.')
            ->set('upload', UploadedFile::fake()->create(
                'dump.log',
                AttachFileToTicket::MAX_SIZE_IN_KILOBYTES + 1,
            ))
            ->call('save')
            ->assertHasErrors('upload');

        $this->assertDatabaseCount('tickets', 0);
        $this->assertDatabaseCount('ticket_attachments', 0);
    }

    public function test_a_file_type_outside_the_allow_list_opens_no_ticket_at_all(): void
    {
        Livewire::actingAs($this->userWith(TicketRole::Requester))
            ->test(TicketForm::class)
            ->set('title', 'Executable slipped in')
            ->set('description', 'The extension is not on the allow list.')
            ->set('upload', UploadedFile::fake()->create('payload.exe', 4))
            ->call('save')
            ->assertHasErrors('upload');

        $this->assertDatabaseCount('tickets', 0);
        $this->assertDatabaseCount('ticket_attachments', 0);
    }

    public function test_the_field_is_offered_only_while_the_ticket_is_being_opened(): void
    {
        $requester = $this->userWith(TicketRole::Requester);

        Livewire::actingAs($requester)
            ->test(TicketForm::class)
            ->assertSee(__('tickets::attachments.hints.optional_at_opening', [
                'size' => AttachFileToTicket::MAX_SIZE_IN_KILOBYTES,
            ]));

        Livewire::actingAs($requester)
            ->test(TicketForm::class, [
                'ticket' => Ticket::factory()->create(['requester_id' => $requester->id]),
            ])
            ->assertDontSee(__('tickets::attachments.hints.optional_at_opening', [
                'size' => AttachFileToTicket::MAX_SIZE_IN_KILOBYTES,
            ]));
    }
}
