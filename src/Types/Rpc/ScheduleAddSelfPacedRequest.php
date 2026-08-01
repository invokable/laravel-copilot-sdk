<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for adding a self-paced (dynamic) schedule.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleAddSelfPacedRequest implements Arrayable
{
    /**
     * @param  string  $prompt  Prompt text enqueued when the model arms the next run.
     * @param  ?string  $displayPrompt  Display-only label for the prompt as shown in the UI.
     */
    public function __construct(
        public string $prompt,
        public ?string $displayPrompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompt: Arr::string($data, 'prompt'),
            displayPrompt: $data['displayPrompt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'prompt' => $this->prompt,
            'displayPrompt' => $this->displayPrompt,
        ], fn ($v) => $v !== null);
    }
}
