<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\NamedProviderConfig;
use Revolution\Copilot\Types\ProviderModelConfig;

/**
 * BYOK providers and/or models to add to the session's registry at runtime.
 *
 * Both fields are optional; provide providers, models, or both.
 *
 * @experimental This type is part of an experimental multi-provider BYOK surface
 * and may change or be removed in future SDK or CLI releases.
 */
readonly class ProviderAddRequest implements Arrayable
{
    /**
     * @param  ProviderModelConfig[]|null  $models     BYOK model definitions to register. Each must reference a provider
     *                                                 that is already registered or included in this same call.
     * @param  NamedProviderConfig[]|null  $providers  Named BYOK provider connections to register, additive to any
     *                                                 providers already in the registry.
     */
    public function __construct(
        public ?array $models = null,
        public ?array $providers = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            models: isset($data['models'])
                ? array_map(fn (array $m) => ProviderModelConfig::fromArray($m), $data['models'])
                : null,
            providers: isset($data['providers'])
                ? array_map(fn (array $p) => NamedProviderConfig::fromArray($p), $data['providers'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'models' => $this->models !== null
                ? array_map(fn (ProviderModelConfig $m) => $m->toArray(), $this->models)
                : null,
            'providers' => $this->providers !== null
                ? array_map(fn (NamedProviderConfig $p) => $p->toArray(), $this->providers)
                : null,
        ], fn ($v) => $v !== null);
    }
}
