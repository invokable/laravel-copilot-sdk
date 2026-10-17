<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Installed plugin record accepted by the server-level sessions API.
 *
 * @see https://github.com/github/copilot-sdk/blob/b96cd2b54f7eb87458fd50f485a751c712e88f62/nodejs/src/generated/rpc.ts
 */
readonly class InstalledPlugin implements Arrayable
{
    public function __construct(
        public string $name,
        public string $marketplace,
        public string $installedAt,
        public bool $enabled,
        public ?string $cachePath = null,
        public ?string $version = null,
        public mixed $source = null,
        public ?string $sourceSha = null,
        public ?string $installedFrom = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            marketplace: Arr::string($data, 'marketplace'),
            version: $data['version'] ?? null,
            installedAt: Arr::string($data, 'installed_at'),
            enabled: Arr::boolean($data, 'enabled'),
            cachePath: isset($data['cache_path']) ? Arr::string($data, 'cache_path') : null,
            source: $data['source'] ?? null,
            sourceSha: $data['source_sha'] ?? null,
            installedFrom: $data['installed_from'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'marketplace' => $this->marketplace,
            'version' => $this->version,
            'installed_at' => $this->installedAt,
            'enabled' => $this->enabled,
            'cache_path' => $this->cachePath,
            'source' => $this->source,
            'source_sha' => $this->sourceSha,
            'installed_from' => $this->installedFrom,
        ], fn ($value): bool => $value !== null);
    }
}
