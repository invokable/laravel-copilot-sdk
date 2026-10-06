<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class GitCurrentBranchRemoteResult implements Arrayable
{
    public function __construct(public string $remote) {}

    public static function fromArray(array $data): self
    {
        return new self(remote: Arr::string($data, 'remote', ''));
    }

    public function toArray(): array
    {
        return ['remote' => $this->remote];
    }
}
