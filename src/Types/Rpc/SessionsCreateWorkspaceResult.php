<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class SessionsCreateWorkspaceResult implements Arrayable
{
    public function __construct(public string $workspaceJson) {}

    public static function fromArray(array $data): self
    {
        return new self(workspaceJson: Arr::string($data, 'workspaceJson', ''));
    }

    public function toArray(): array
    {
        return ['workspaceJson' => $this->workspaceJson];
    }
}
