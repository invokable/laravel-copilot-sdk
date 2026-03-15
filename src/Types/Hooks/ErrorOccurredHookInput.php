<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for error-occurred hook.
 */
readonly class ErrorOccurredHookInput extends BaseHookInput
{
    /**
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     * @param  string  $error  Error message
     * @param  string  $errorContext  Error context: "model_call", "tool_execution", "system", or "user_input"
     * @param  bool  $recoverable  Whether the error is recoverable
     */
    public function __construct(
        int $timestamp,
        string $cwd,
        public string $error,
        public string $errorContext,
        public bool $recoverable,
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
            error: $data['error'] ?? '',
            errorContext: $data['errorContext'] ?? 'system',
            recoverable: $data['recoverable'] ?? false,
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
