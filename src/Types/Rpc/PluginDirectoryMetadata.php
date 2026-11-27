<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class PluginDirectoryMetadata implements Arrayable
{
    public function __construct(
        public string $pluginPath,
        public ?string $canonicalDirectory = null,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            pluginPath: Arr::string($data, 'pluginPath'),
            canonicalDirectory: isset($data['canonicalDirectory']) ? Arr::string($data, 'canonicalDirectory') : null,
            description: isset($data['description']) ? Arr::string($data, 'description') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'pluginPath' => $this->pluginPath,
            'canonicalDirectory' => $this->canonicalDirectory,
            'description' => $this->description,
        ], fn ($value): bool => $value !== null);
    }
}
