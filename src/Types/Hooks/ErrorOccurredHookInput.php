<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

/**
 * Input for error-occurred hook.
 */
readonly class ErrorOccurredHookInput extends BaseHookInput
{
    public function __construct(
        int $timestamp,
        string $cwd,
        /**
         * Error message.
         */
        public string $error,
        /**
         * Error context: "model_call", "tool_execution", "system", or "user_input".
         */
        public string $errorContext,
        /**
         * Whether the error is recoverable.
         */
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
