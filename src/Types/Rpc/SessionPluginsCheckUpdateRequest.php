<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Identifies an installed plugin and its configured marketplace for an update check.
 *
 * @experimental
 */
readonly class SessionPluginsCheckUpdateRequest implements Arrayable
{
    public function __construct(
        public string $name,
        public string $marketplace,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            marketplace: Arr::string($data, 'marketplace'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'marketplace' => $this->marketplace,
        ];
    }
}
