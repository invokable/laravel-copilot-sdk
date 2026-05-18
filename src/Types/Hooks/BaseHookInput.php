<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Base interface for all hook inputs.
 */
readonly class BaseHookInput implements Arrayable
{
    /**
     * @param  string  $sessionId  The runtime session ID of the session that triggered the hook.
     *                             For sub-agent hooks this differs from the invocation session ID.
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     */
    public function __construct(
        public string $sessionId,
        public int $timestamp,
        public string $cwd,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            sessionId: $data['sessionId'] ?? '',
            timestamp: $data['timestamp'] ?? 0,
            cwd: $data['cwd'] ?? '',
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'timestamp' => $this->timestamp,
            'cwd' => $this->cwd,
        ];
    }
}
