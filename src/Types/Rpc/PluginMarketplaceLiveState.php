<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Live state of an installed plugin marketplace entry.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginMarketplaceLiveState implements Arrayable
{
    public function __construct(
        public bool $enabled,
        public string $installedFrom,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: Arr::boolean($data, 'enabled'),
            installedFrom: Arr::string($data, 'installedFrom'),
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'installedFrom' => $this->installedFrom,
        ];
    }
}
