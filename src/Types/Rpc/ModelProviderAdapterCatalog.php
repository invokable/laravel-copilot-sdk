<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Provider adapters available to the current session. */
readonly class ModelProviderAdapterCatalog implements Arrayable
{
    /** @param ModelProviderAdapterDescriptor[] $providers */
    public function __construct(public array $providers = []) {}

    public static function fromArray(array $data): self
    {
        return new self(providers: array_map(
            fn (array $provider) => ModelProviderAdapterDescriptor::fromArray($provider),
            $data['providers'] ?? [],
        ));
    }

    public function toArray(): array
    {
        return ['providers' => array_map(
            fn (ModelProviderAdapterDescriptor $provider) => $provider->toArray(),
            $this->providers,
        )];
    }
}
