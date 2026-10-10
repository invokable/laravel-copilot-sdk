<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ManagedPluginProgressPhase;

/** Progress payload for organization-required plugin preparation. */
readonly class ManagedPluginProgressData implements Arrayable
{
    /** @param string[] $pluginSpecs */
    public function __construct(
        public ManagedPluginProgressPhase|string $phase,
        public array $pluginSpecs = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $phase = $data['phase'] ?? 'initializing';

        return new self(
            phase: ManagedPluginProgressPhase::tryFrom($phase) ?? $phase,
            pluginSpecs: $data['pluginSpecs'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'phase' => $this->phase instanceof ManagedPluginProgressPhase
                ? $this->phase->value
                : $this->phase,
            'pluginSpecs' => $this->pluginSpecs,
        ];
    }
}
