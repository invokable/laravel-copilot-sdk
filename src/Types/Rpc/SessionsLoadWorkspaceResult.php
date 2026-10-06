<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class SessionsLoadWorkspaceResult implements Arrayable
{
    public function __construct(public ?string $workspaceJson = null) {}

    public static function fromArray(array $data): self
    {
        return new self(workspaceJson: $data['workspaceJson'] ?? null);
    }

    public function toArray(): array
    {
        return ['workspaceJson' => $this->workspaceJson];
    }
}
