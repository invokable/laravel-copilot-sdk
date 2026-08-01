<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for adding a one-shot absolute-time schedule.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleAddAtRequest implements Arrayable
{
    /**
     * @param  int  $at  Absolute fire time, in epoch milliseconds.
     * @param  string  $prompt  Prompt text enqueued when the schedule fires.
     * @param  ?bool  $recurring  Whether the schedule re-arms after firing.
     * @param  ?string  $displayPrompt  Display-only label for the prompt as shown in the UI.
     */
    public function __construct(
        public int $at,
        public string $prompt,
        public ?bool $recurring = null,
        public ?string $displayPrompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            at: Arr::integer($data, 'at'),
            prompt: Arr::string($data, 'prompt'),
            recurring: $data['recurring'] ?? null,
            displayPrompt: $data['displayPrompt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'at' => $this->at,
            'prompt' => $this->prompt,
            'recurring' => $this->recurring,
            'displayPrompt' => $this->displayPrompt,
        ], fn ($v) => $v !== null);
    }
}
