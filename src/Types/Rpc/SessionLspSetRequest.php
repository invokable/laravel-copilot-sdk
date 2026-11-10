<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A persistent user-level server toggle.
 *
 * @experimental
 */
readonly class SessionLspSetRequest implements Arrayable
{
    public function __construct(
        public string $serverId,
        public bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverId: Arr::string($data, 'serverId', ''),
            enabled: Arr::boolean($data, 'enabled', false),
        );
    }

    public function toArray(): array
    {
        return [
            'serverId' => $this->serverId,
            'enabled' => $this->enabled,
        ];
    }
}
