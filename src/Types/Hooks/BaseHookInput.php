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
     * @param  int  $timestamp  Unix timestamp in milliseconds when the hook was triggered
     * @param  string  $cwd  Current working directory
     */
    public function __construct(
        public int $timestamp,
        public string $cwd,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
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
            'timestamp' => $this->timestamp,
            'cwd' => $this->cwd,
        ];
    }
}
