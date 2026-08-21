<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpServerCardMediaType;

/**
 * Provenance of the validated JSON MCP card bound to a completed plan.
 *
 * @experimental
 */
readonly class McpPlanProvenance implements Arrayable
{
    public function __construct(
        public string $authority,
        public string $validatedAt,
        public CardDigest $cardDigest,
        public McpServerCardMediaType $mediaType,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            authority: $data['authority'],
            validatedAt: $data['validatedAt'],
            cardDigest: CardDigest::fromArray($data['cardDigest']),
            mediaType: McpServerCardMediaType::from($data['mediaType']),
        );
    }

    public function toArray(): array
    {
        return [
            'authority' => $this->authority,
            'validatedAt' => $this->validatedAt,
            'cardDigest' => $this->cardDigest->toArray(),
            'mediaType' => $this->mediaType->value,
        ];
    }
}
