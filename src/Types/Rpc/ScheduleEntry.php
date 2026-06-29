<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * An active scheduled prompt entry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleEntry implements Arrayable
{
    /**
     * @param  int  $id  Sequential id assigned by the runtime within the session
     * @param  int  $intervalMs  Interval between scheduled ticks, in milliseconds
     * @param  string  $nextRunAt  ISO 8601 timestamp when the next tick is scheduled to fire
     * @param  string  $prompt  Prompt text that gets enqueued on every tick
     * @param  bool  $recurring  Whether the schedule re-arms after each tick (`/every`) or fires once (`/after`)
     * @param  ?bool  $selfPaced  True for a self-paced (`dynamic`) schedule: no fixed cadence; the model arms each next run via the `manage_schedule` `wakeup` action.
     * @param  ?string  $displayPrompt  Display-only label for the prompt as shown in the UI
     */
    public function __construct(
        public int $id,
        public int $intervalMs,
        public string $nextRunAt,
        public string $prompt,
        public bool $recurring,
        public ?bool $selfPaced = null,
        public ?string $displayPrompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::integer($data, 'id', 0),
            intervalMs: Arr::integer($data, 'intervalMs', 0),
            nextRunAt: Arr::string($data, 'nextRunAt', ''),
            prompt: Arr::string($data, 'prompt', ''),
            recurring: Arr::boolean($data, 'recurring', false),
            selfPaced: $data['selfPaced'] ?? null,
            displayPrompt: $data['displayPrompt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'intervalMs' => $this->intervalMs,
            'nextRunAt' => $this->nextRunAt,
            'prompt' => $this->prompt,
            'recurring' => $this->recurring,
            'selfPaced' => $this->selfPaced,
            'displayPrompt' => $this->displayPrompt,
        ], fn ($v) => $v !== null);
    }
}
