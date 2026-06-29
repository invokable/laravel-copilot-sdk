<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Support\Arr;

/**
 * Input for error-occurred hook.
 */
readonly class ErrorOccurredHookInput extends BaseHookInput
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $error  Error message
     * @param  string  $errorContext  Error context: "model_call", "tool_execution", "system", or "user_input"
     * @param  bool  $recoverable  Whether the error is recoverable
     */
    public function __construct(
        string $sessionId,
        int $timestamp,
        string $cwd,
        public string $error,
        public string $errorContext,
        public bool $recoverable,
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
            error: Arr::string($data, 'error', ''),
            errorContext: Arr::string($data, 'errorContext', 'system'),
            recoverable: Arr::boolean($data, 'recoverable', false),
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'error' => $this->error,
            'errorContext' => $this->errorContext,
            'recoverable' => $this->recoverable,
        ];
    }
}
