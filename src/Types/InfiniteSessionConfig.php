<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration for infinite sessions with automatic context compaction and workspace persistence.
 * When enabled, sessions automatically manage context window limits through background compaction
 * and persist state to a workspace directory.
 */
readonly class InfiniteSessionConfig implements Arrayable
{
    public function __construct(
        /**
         * Whether infinite sessions are enabled.
         * Default: true
         */
        public ?bool $enabled = null,
        /**
         * Context utilization threshold (0.0-1.0) at which background compaction starts.
         * Compaction runs asynchronously, allowing the session to continue processing.
         * Default: 0.80
         */
        public ?float $backgroundCompactionThreshold = null,
        /**
         * Context utilization threshold (0.0-1.0) at which the session blocks until compaction completes.
         * This prevents context overflow when compaction hasn't finished in time.
         * Default: 0.95
         */
        public ?float $bufferExhaustionThreshold = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: $data['enabled'] ?? null,
            backgroundCompactionThreshold: $data['backgroundCompactionThreshold'] ?? null,
            bufferExhaustionThreshold: $data['bufferExhaustionThreshold'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'backgroundCompactionThreshold' => $this->backgroundCompactionThreshold,
            'bufferExhaustionThreshold' => $this->bufferExhaustionThreshold,
        ], fn ($value) => $value !== null);
    }
}
