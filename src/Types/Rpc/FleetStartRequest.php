<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for starting fleet orchestration: an optional user prompt combined with the fleet
 * instructions, plus the send options forwarded to the resulting turn.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FleetStartRequest implements Arrayable
{
    /**
     * @param  ?string  $prompt  Optional user prompt to combine with fleet instructions
     * @param  ?array  $attachments  Optional attachments (files, directories, selections, blobs, GitHub references) to include with the fleet request
     * @param  ?bool  $billable  If false, this request will not trigger a Premium Request Unit charge. User requests default to billable.
     * @param  ?bool  $wait  If true, await completion of the agentic loop for this fleet request before returning. Defaults to false.
     */
    public function __construct(
        public ?string $prompt = null,
        public ?array $attachments = null,
        public ?bool $billable = null,
        public ?bool $wait = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompt: $data['prompt'] ?? null,
            attachments: $data['attachments'] ?? null,
            billable: $data['billable'] ?? null,
            wait: $data['wait'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'prompt' => $this->prompt,
            'attachments' => $this->attachments,
            'billable' => $this->billable,
            'wait' => $this->wait,
        ], fn ($v) => $v !== null);
    }
}
