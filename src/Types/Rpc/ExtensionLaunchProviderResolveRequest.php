<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ExtensionSource;

/**
 * Inbound request to resolve how an extension entrypoint should be launched.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionLaunchProviderResolveRequest implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public string $modulePath,
        public ExtensionSource|string $source,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = Arr::string($data, 'source');

        return new self(
            id: Arr::string($data, 'id'),
            name: Arr::string($data, 'name'),
            modulePath: Arr::string($data, 'modulePath'),
            source: ExtensionSource::tryFrom($source) ?? $source,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'modulePath' => $this->modulePath,
            'source' => $this->source instanceof ExtensionSource ? $this->source->value : $this->source,
        ];
    }
}
