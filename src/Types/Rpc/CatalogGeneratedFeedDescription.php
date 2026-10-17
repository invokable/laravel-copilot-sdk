<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Identity and freshness metadata for a generated catalog feed.
 *
 * @experimental
 */
readonly class CatalogGeneratedFeedDescription implements Arrayable
{
    public function __construct(
        public string $strategy,
        public string $key,
        public ?string $generatedAt = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            strategy: $data['strategy'],
            key: $data['key'],
            generatedAt: $data['generatedAt'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'strategy' => $this->strategy,
            'key' => $this->key,
        ];

        if ($this->generatedAt !== null) {
            $data['generatedAt'] = $this->generatedAt;
        }

        return $data;
    }
}
