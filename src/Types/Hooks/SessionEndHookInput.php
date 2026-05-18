<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for session-end hook.
 */
readonly class SessionEndHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $reason  Reason for ending: "complete", "error", "abort", "timeout", or "user_exit"
     * @param  ?string  $finalMessage  Final message, if any
     * @param  ?string  $error  Error message, if any
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        public string $reason,
        public ?string $finalMessage = null,
        public ?string $error = null,
    ) {
        parent::__construct($sessionId, $timestamp, $cwd);
    }

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: $data['sessionId'] ?? '',
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
