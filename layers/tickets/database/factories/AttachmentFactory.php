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
        $stem = faker()->words(2);
        $reference = faker()->uuid();

        return [
            'ticket_id' => Ticket::factory(),
            'uploaded_by_id' => User::factory(),
            'disk' => AttachFileToTicket::DISK,
            'path' => "ticket-attachments/{$reference}.txt",
            'original_name' => "{$stem}.txt",
            'mime_type' => 'text/plain',
            'size_in_bytes' => faker()->number(200, 40000),
        ];
    }

    /**
     * Also writes the bytes, so a seeded row points at a file that exists.
     */
    public function stored(): static
    {
        return $this->afterCreating(function (Attachment $attachment): void {
            Storage::disk($attachment->disk)->put($attachment->path, faker()->paragraphs(1));
        });
    }
}
