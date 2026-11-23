<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPluginActivationSnapshot implements Arrayable
{
    /**
     * @param  InstalledPlugin[]  $plugins
     * @param  UIPluginActivationDecision[]  $inventory
     * @param  UIPluginActivationWarning[]  $warnings
     * @param  ?string[]  $repositoryLivePluginPaths
     */
    public function __construct(
        public array $plugins,
        public array $inventory,
        public array $warnings,
        public UIPluginActivationContext $context,
        public string $fingerprint,
        public ?array $repositoryLivePluginPaths = null,
        public ?string $resolutionFailure = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugins: array_map(
                static fn (array $plugin): InstalledPlugin => InstalledPlugin::fromArray($plugin),
                array_values(Arr::array($data, 'plugins', [])),
            ),
            inventory: array_map(
                static fn (array $decision): UIPluginActivationDecision => UIPluginActivationDecision::fromArray($decision),
                array_values(Arr::array($data, 'inventory', [])),
            ),
            warnings: array_map(
                static fn (array $warning): UIPluginActivationWarning => UIPluginActivationWarning::fromArray($warning),
                array_values(Arr::array($data, 'warnings', [])),
            ),
            context: UIPluginActivationContext::fromArray(Arr::array($data, 'context', [])),
            fingerprint: Arr::string($data, 'fingerprint', ''),
            repositoryLivePluginPaths: isset($data['repositoryLivePluginPaths'])
                ? array_values(Arr::array($data, 'repositoryLivePluginPaths'))
                : null,
            resolutionFailure: isset($data['resolutionFailure']) ? Arr::string($data, 'resolutionFailure') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'plugins' => array_map(static fn (InstalledPlugin $plugin): array => $plugin->toArray(), $this->plugins),
            'inventory' => array_map(static fn (UIPluginActivationDecision $decision): array => $decision->toArray(), $this->inventory),
            'warnings' => array_map(static fn (UIPluginActivationWarning $warning): array => $warning->toArray(), $this->warnings),
            'context' => $this->context->toArray(),
            'fingerprint' => $this->fingerprint,
            'repositoryLivePluginPaths' => $this->repositoryLivePluginPaths,
            'resolutionFailure' => $this->resolutionFailure,
        ], static fn ($value): bool => $value !== null);
    }
}
