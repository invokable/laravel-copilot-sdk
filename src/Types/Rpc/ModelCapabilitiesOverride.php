<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Override individual model capabilities resolved by the runtime.
 *
 * All properties are optional — only the supplied fields are deep-merged
 * over the runtime defaults.
 */
readonly class ModelCapabilitiesOverride implements Arrayable
{
    /**
     * @param  ModelCapabilitiesOverrideSupports|array|null  $supports  Feature flag overrides
     * @param  ModelCapabilitiesOverrideLimits|array|null  $limits  Token limit overrides
     */
    public function __construct(
        public ModelCapabilitiesOverrideSupports|array|null $supports = null,
        public ModelCapabilitiesOverrideLimits|array|null $limits = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $supports = isset($data['supports'])
            ? ($data['supports'] instanceof ModelCapabilitiesOverrideSupports
                ? $data['supports']
                : ModelCapabilitiesOverrideSupports::fromArray($data['supports']))
            : null;

        $limits = isset($data['limits'])
            ? ($data['limits'] instanceof ModelCapabilitiesOverrideLimits
                ? $data['limits']
                : ModelCapabilitiesOverrideLimits::fromArray($data['limits']))
            : null;

        return new self(
            supports: $supports,
            limits: $limits,
        );
    }

    public function toArray(): array
    {
        $supports = $this->supports instanceof ModelCapabilitiesOverrideSupports
            ? $this->supports->toArray()
            : $this->supports;

        $limits = $this->limits instanceof ModelCapabilitiesOverrideLimits
            ? $this->limits->toArray()
            : $this->limits;

        return array_filter([
            'supports' => $supports,
            'limits' => $limits,
        ], fn ($v) => $v !== null);
    }
}
