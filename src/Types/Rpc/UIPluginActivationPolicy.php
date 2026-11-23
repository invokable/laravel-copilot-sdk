<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPluginActivationPolicy implements Arrayable
{
    /**
     * @param  InstalledPlugin[]  $explicitPlugins
     * @param  ?InstalledPlugin[]  $ambientPlugins
     * @param  ?array<string, bool>  $baseEnabledPlugins
     * @param  ?array<string, bool>  $defaultEnabledPlugins
     */
    public function __construct(
        public bool $includeAmbient,
        public array $explicitPlugins,
        public UIPluginTrustContext $trust,
        public ?array $ambientPlugins = null,
        public ?array $baseEnabledPlugins = null,
        public ?array $defaultEnabledPlugins = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $parsePlugins = static fn (array $plugins): array => array_map(
            static fn (array $plugin): InstalledPlugin => InstalledPlugin::fromArray($plugin),
            array_values($plugins),
        );

        return new self(
            includeAmbient: Arr::boolean($data, 'includeAmbient', false),
            explicitPlugins: $parsePlugins(Arr::array($data, 'explicitPlugins', [])),
            trust: UIPluginTrustContext::fromArray(Arr::array($data, 'trust', [])),
            ambientPlugins: isset($data['ambientPlugins']) ? $parsePlugins(Arr::array($data, 'ambientPlugins')) : null,
            baseEnabledPlugins: isset($data['baseEnabledPlugins']) ? Arr::array($data, 'baseEnabledPlugins') : null,
            defaultEnabledPlugins: isset($data['defaultEnabledPlugins']) ? Arr::array($data, 'defaultEnabledPlugins') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'includeAmbient' => $this->includeAmbient,
            'explicitPlugins' => array_map(static fn (InstalledPlugin $plugin): array => $plugin->toArray(), $this->explicitPlugins),
            'trust' => $this->trust->toArray(),
            'ambientPlugins' => $this->ambientPlugins === null
                ? null
                : array_map(static fn (InstalledPlugin $plugin): array => $plugin->toArray(), $this->ambientPlugins),
            'baseEnabledPlugins' => $this->baseEnabledPlugins,
            'defaultEnabledPlugins' => $this->defaultEnabledPlugins,
        ], static fn ($value): bool => $value !== null);
    }
}
