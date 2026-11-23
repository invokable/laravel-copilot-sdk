<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPluginActivationDecision implements Arrayable
{
    public function __construct(
        public InstalledPlugin $plugin,
        public bool $active,
        public string $reason,
        public bool $persisted,
        public ?string $source = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            plugin: InstalledPlugin::fromArray(Arr::array($data, 'plugin', [])),
            active: Arr::boolean($data, 'active', false),
            source: isset($data['source']) ? Arr::string($data, 'source') : null,
            reason: Arr::string($data, 'reason', ''),
            persisted: Arr::boolean($data, 'persisted', false),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'plugin' => $this->plugin->toArray(),
            'active' => $this->active,
            'source' => $this->source,
            'reason' => $this->reason,
            'persisted' => $this->persisted,
        ], static fn ($value): bool => $value !== null);
    }
}
