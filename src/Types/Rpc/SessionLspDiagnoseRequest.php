<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A server selected for a startup probe.
 *
 * @experimental
 */
readonly class SessionLspDiagnoseRequest implements Arrayable
{
    public function __construct(
        public string $serverId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverId: Arr::string($data, 'serverId', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'serverId' => $this->serverId,
        ];
    }
}
