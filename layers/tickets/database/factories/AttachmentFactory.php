<?php

namespace Tickets\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Tickets\Actions\AttachFileToTicket;
use Tickets\Models\Attachment;
use Tickets\Models\Ticket;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = faker()->words(2).'.txt';

        return [
            'ticket_id' => Ticket::factory(),
            'uploaded_by_id' => User::factory(),
            'disk' => AttachFileToTicket::DISK,
            'path' => 'ticket-attachments/'.faker()->uuid().'.txt',
            'original_name' => $name,
            'mime_type' => 'text/plain',
            'size_in_bytes' => faker()->number(200, 40000),
        ];
    }

    /**
     * Also writes the bytes, so a seeded environment has files that really
     * exist behind their rows.
     */
    public function stored(): static
    {
        return $this->afterCreating(function (Attachment $attachment): void {
            Storage::disk($attachment->disk)->put($attachment->path, faker()->paragraphs(1));
        });
    }
}
