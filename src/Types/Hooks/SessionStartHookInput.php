<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for session-start hook.
 */
readonly class SessionStartHookInput extends BaseHookInput
{
    /**
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $source  Source of the session: "startup", "resume", or "new"
     * @param  ?string  $initialPrompt  Initial prompt, if any
     */
    public function __construct(
        int $timestamp,
        string $cwd,
        public string $source,
        public ?string $initialPrompt = null,
    ) {
        parent::__construct($timestamp, $cwd);
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
            source: $data['source'] ?? 'new',
            initialPrompt: $data['initialPrompt'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'source' => $this->source,
            'initialPrompt' => $this->initialPrompt,
        ], fn ($value) => $value !== null);
    }
}
