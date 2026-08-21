<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CardDigestAlgorithm;

/**
 * Content digest of a validated MCP server card.
 *
 * @experimental
 */
readonly class CardDigest implements Arrayable
{
    public function __construct(
        public CardDigestAlgorithm $algorithm,
        public string $value,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            algorithm: CardDigestAlgorithm::from($data['algorithm']),
            value: $data['value'],
        );
    }

    public function toArray(): array
    {
        return [
            'algorithm' => $this->algorithm->value,
            'value' => $this->value,
        ];
    }
}
