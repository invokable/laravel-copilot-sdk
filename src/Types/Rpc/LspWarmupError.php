<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A server whose warmup failed.
 *
 * @experimental
 */
readonly class LspWarmupError implements Arrayable
{
    public function __construct(
        public string $serverId,
        public string $error,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverId: Arr::string($data, 'serverId', ''),
            error: Arr::string($data, 'error', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'serverId' => $this->serverId,
            'error' => $this->error,
        ];
    }
}
