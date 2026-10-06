<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class GitHubOwnersCancelResult implements Arrayable
{
    public function __construct(public bool $canceled) {}

    public static function fromArray(array $data): self
    {
        return new self(canceled: (bool) ($data['canceled'] ?? false));
    }

    public function toArray(): array
    {
        return ['canceled' => $this->canceled];
    }
}
