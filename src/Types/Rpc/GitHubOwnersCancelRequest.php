<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class GitHubOwnersCancelRequest implements Arrayable
{
    public function __construct(public int $requestId) {}

    public static function fromArray(array $data): self
    {
        return new self(requestId: (int) ($data['requestId'] ?? 0));
    }

    public function toArray(): array
    {
        return ['requestId' => $this->requestId];
    }
}
