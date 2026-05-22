<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Hooks;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Output for pre-MCP-tool-call hook.
 *
 * Allows modification of the tool call metadata before it is dispatched.
 *
 * metaToUse semantics:
 * - Property not set or null: preserve the current request _meta
 * - Empty array []: omit _meta from the request
 * - Non-empty array: use this array as request _meta
 */
readonly class PreMcpToolCallHookOutput implements Arrayable
{
    /**
     * @param  array<string, mixed>|null  $metaToUse  Metadata to use for the tool call
     */
    public function __construct(
        public ?array $metaToUse = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            metaToUse: $data['metaToUse'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        // Only include metaToUse if it was explicitly set
        if ($this->metaToUse !== null) {
            return ['metaToUse' => $this->metaToUse];
        }

        return [];
    }
}
