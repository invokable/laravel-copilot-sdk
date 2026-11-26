<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class AgentsValidateSpawnCwdRequest implements Arrayable
{
    public function __construct(public string $cwd) {}

    public static function fromArray(array $data): self
    {
        return new self(cwd: Arr::string($data, 'cwd'));
    }

    public function toArray(): array
    {
        return ['cwd' => $this->cwd];
    }
}
