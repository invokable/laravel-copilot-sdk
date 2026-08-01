<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for adding a relative-interval schedule.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleAddRequest implements Arrayable
{
    /**
     * @param  string  $interval  Interval between ticks, expressed as a human duration (e.g. `30m`).
     * @param  string  $prompt  Prompt text enqueued on every tick.
     * @param  ?bool  $recurring  Whether the schedule re-arms after each tick (`/every`) or fires once (`/after`).
     * @param  ?string  $displayPrompt  Display-only label for the prompt as shown in the UI.
     */
    public function __construct(
        public string $interval,
        public string $prompt,
        public ?bool $recurring = null,
        public ?string $displayPrompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            interval: Arr::string($data, 'interval'),
            prompt: Arr::string($data, 'prompt'),
            recurring: $data['recurring'] ?? null,
            displayPrompt: $data['displayPrompt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'interval' => $this->interval,
            'prompt' => $this->prompt,
            'recurring' => $this->recurring,
            'displayPrompt' => $this->displayPrompt,
        ], fn ($v) => $v !== null);
    }
}
