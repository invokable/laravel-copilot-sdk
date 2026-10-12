<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Opaque reference to the MCP server card used by an installation plan.
 *
 * @experimental
 */
readonly class McpServerCardReference implements Arrayable
{
    public function __construct(
        public McpServerCardUrl|McpServerCardEmbedded $card,
    ) {}

    public static function fromArray(array $data): static
    {
        $card = match ($data['kind']) {
            'url' => McpServerCardUrl::fromArray($data),
            'embedded' => McpServerCardEmbedded::fromArray($data),
            default => throw new \ValueError('Unknown MCP server card reference kind.'),
        };

        return new static(card: $card);
    }

    public function toArray(): array
    {
        return $this->card->toArray();
    }
}
