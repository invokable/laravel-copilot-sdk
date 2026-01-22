<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * System message configuration for session creation.
 * - Append mode (default): SDK foundation + optional custom content
 * - Replace mode: Full control, caller provides entire system message
 */
readonly class SystemMessageConfig implements Arrayable
{
    public function __construct(
        /**
         * Mode: "append" (default) or "replace".
         */
        public ?string $mode = null,
        /**
         * Content to append or replace with.
         */
        public ?string $content = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            mode: $data['mode'] ?? null,
            content: $data['content'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode,
            'content' => $this->content,
        ], fn ($value) => $value !== null);
    }
}
