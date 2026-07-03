<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A per-source attribution entry in the context window breakdown.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionContextAttributionEntry implements Arrayable
{
    /**
     * @param  string  $id  Identifier for this entry (e.g. `tool:bash`, `skill:tmux`). Unique within the snapshot.
     * @param  string  $kind  Source category (e.g. `skill`, `subagent`, `mcpServer`, `tool`, `system`, `toolDefinition`, `plugin`). Not a closed set.
     * @param  string  $label  Human-readable display label. Presentation-only.
     * @param  int  $tokens  Token count attributable to this entry.
     * @param  ?string  $parentId  Optional parent entry `id` for nesting.
     * @param  ?array<string, string>  $attributes  Supplementary per-entry metadata (values are stringified).
     */
    public function __construct(
        public string $id,
        public string $kind,
        public string $label,
        public int $tokens,
        public ?string $parentId = null,
        public ?array $attributes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            kind: Arr::string($data, 'kind', ''),
            label: Arr::string($data, 'label', ''),
            tokens: Arr::integer($data, 'tokens', 0),
            parentId: $data['parentId'] ?? null,
            attributes: $data['attributes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'kind' => $this->kind,
            'label' => $this->label,
            'tokens' => $this->tokens,
            'parentId' => $this->parentId,
            'attributes' => $this->attributes,
        ], fn ($value): bool => $value !== null);
    }
}
