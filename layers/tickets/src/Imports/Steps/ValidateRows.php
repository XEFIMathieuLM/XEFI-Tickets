<?php

namespace Tickets\Imports\Steps;

use Closure;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tickets\Enums\TicketPriority;
use Tickets\Imports\ImportBatch;

/**
 * Judges each row on its own. A bad row leaves the batch with its reason; it
 * never stops the ones that follow.
 */
class ValidateRows
{
    public function handle(ImportBatch $batch, Closure $next): ImportBatch
    {
        foreach ($batch->rows as $line => $row) {
            $validator = Validator::make($row, $this->rules());

            if ($validator->fails()) {
                $batch->reject($line, implode(' ', $validator->errors()->all()));
            }
        }

        return $next($batch);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'requester_email' => ['required', 'email'],
        ];
    }
}
