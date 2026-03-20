<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * System message configuration for session creation.
 * - Append mode (default): SDK foundation + optional custom content
 * - Replace mode: Full control, caller provides entire system message
 * - Customize mode: Section-level overrides with graceful fallback
 */
readonly class SystemMessageConfig implements Arrayable
{
    /**
     * @param  ?string  $mode  Mode: "append" (default), "replace", or "customize"
     * @param  ?string  $content  Content to append or replace with
     * @param  ?array<string, SectionOverride|array>  $sections  Section overrides (customize mode only)
     */
    public function __construct(
        public ?string $mode = null,
        public ?string $content = null,
        public ?array $sections = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        $sections = null;
        if (isset($data['sections']) && is_array($data['sections'])) {
            $sections = array_map(
                fn (array $section) => SectionOverride::fromArray($section),
                $data['sections'],
            );
        }

        return new self(
            mode: $data['mode'] ?? null,
            content: $data['content'] ?? null,
            sections: $sections,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $result = array_filter([
            'mode' => $this->mode,
            'content' => $this->content,
        ], fn ($value) => $value !== null);

        if ($this->sections !== null) {
            $result['sections'] = array_map(
                fn (SectionOverride|array $section) => $section instanceof SectionOverride ? $section->toArray() : $section,
                $this->sections,
            );
        }

        return $result;
    }
}
