<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for session-end hook.
 */
readonly class SessionEndHookInput extends BaseHookInput
{
    public function __construct(
        int $timestamp,
        string $cwd,
        /**
         * Reason for ending: "complete", "error", "abort", "timeout", or "user_exit".
         */
        public string $reason,
        /**
         * Final message, if any.
         */
        public ?string $finalMessage = null,
        /**
         * Error message, if any.
         */
        public ?string $error = null,
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
            reason: $data['reason'] ?? 'complete',
            finalMessage: $data['finalMessage'] ?? null,
            error: $data['error'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'reason' => $this->reason,
            'finalMessage' => $this->finalMessage,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
