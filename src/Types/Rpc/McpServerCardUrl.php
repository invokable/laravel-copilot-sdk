<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpServerCardMediaType;
use Revolution\Copilot\Enums\McpServerCardUrlKind;

/**
 * An MCP server card to be retrieved from a URL.
 *
 * @experimental
 */
readonly class McpServerCardUrl implements Arrayable
{
    public function __construct(
        public McpServerCardUrlKind $kind,
        public McpServerCardMediaType $mediaType,
        public string $url,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: McpServerCardUrlKind::from($data['kind']),
            mediaType: McpServerCardMediaType::from($data['mediaType']),
            url: $data['url'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind->value,
            'mediaType' => $this->mediaType->value,
            'url' => $this->url,
        ];
    }
}
