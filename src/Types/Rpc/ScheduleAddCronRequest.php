<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for adding a recurring calendar (cron) schedule.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ScheduleAddCronRequest implements Arrayable
{
    /**
     * @param  string  $cron  5-field cron expression, evaluated in `tz`.
     * @param  string  $prompt  Prompt text enqueued on every tick.
     * @param  ?bool  $recurring  Whether the schedule re-arms after each tick.
     * @param  ?string  $displayPrompt  Display-only label for the prompt as shown in the UI.
     * @param  ?string  $tz  IANA timezone the `cron` expression is evaluated in.
     */
    public function __construct(
        public string $cron,
        public string $prompt,
        public ?bool $recurring = null,
        public ?string $displayPrompt = null,
        public ?string $tz = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cron: Arr::string($data, 'cron'),
            prompt: Arr::string($data, 'prompt'),
            recurring: $data['recurring'] ?? null,
            displayPrompt: $data['displayPrompt'] ?? null,
            tz: $data['tz'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cron' => $this->cron,
            'prompt' => $this->prompt,
            'recurring' => $this->recurring,
            'displayPrompt' => $this->displayPrompt,
            'tz' => $this->tz,
        ], fn ($v) => $v !== null);
    }
}
