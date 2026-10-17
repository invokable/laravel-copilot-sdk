<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Identity and version of a saved catalog feed.
 *
 * @experimental
 */
readonly class CatalogSavedFeedDescription implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public int $version,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            version: (int) $data['version'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}
