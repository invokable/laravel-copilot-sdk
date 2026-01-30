<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Base interface for all hook inputs.
 */
readonly class BaseHookInput implements Arrayable
{
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
