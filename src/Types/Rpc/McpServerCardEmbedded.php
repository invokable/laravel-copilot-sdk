<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpServerCardEmbeddedKind;
use Revolution\Copilot\Enums\McpServerCardMediaType;

/**
 * An MCP server card supplied inline as an inert document.
 *
 * @experimental
 */
readonly class McpServerCardEmbedded implements Arrayable
{
    public function __construct(
        public McpServerCardEmbeddedKind $kind,
        public McpServerCardMediaType $mediaType,
        public string $data,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: McpServerCardEmbeddedKind::from($data['kind']),
            mediaType: McpServerCardMediaType::from($data['mediaType']),
            data: $data['data'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind->value,
            'mediaType' => $this->mediaType->value,
            'data' => $this->data,
        ];
    }
}
