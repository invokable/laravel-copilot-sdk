<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for session-start hook.
 */
readonly class SessionStartHookInput extends BaseHookInput
{
    public function __construct(
        int $timestamp,
        string $cwd,
        /**
         * Source of the session: "startup", "resume", or "new".
         */
        public string $source,
        /**
         * Initial prompt, if any.
         */
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
