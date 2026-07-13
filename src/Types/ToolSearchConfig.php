<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * SDK-supplied override for the runtime's built-in tool-search behavior.
 *
 * Tool search lets the model discover tools on demand instead of loading every
 * tool definition up front. When the total tool count exceeds the deferral
 * threshold, MCP and external tools are marked as deferred and surfaced through
 * the built-in `tool_search_tool`.
 */
readonly class ToolSearchConfig implements Arrayable
{
    /**
     * @param  ?bool  $enabled  Toggle to enable/disable tool search.
     * @param  ?int  $deferThreshold  Overrides the total tool count at which MCP and external tools are automatically deferred behind tool search. Defaults to the built-in threshold (30) when omitted.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?int $deferThreshold = null,
    ) {}

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: isset($data['enabled']) ? (bool) $data['enabled'] : null,
            deferThreshold: isset($data['deferThreshold']) ? (int) $data['deferThreshold'] : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'deferThreshold' => $this->deferThreshold,
        ], fn ($v) => $v !== null);
    }
}
