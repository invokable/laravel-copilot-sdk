<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class GitHubOwnerOption implements Arrayable
{
    public function __construct(
        public string $login,
        public string $type,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(login: $data['login'] ?? '', type: $data['type'] ?? '');
    }

    public function toArray(): array
    {
        return ['login' => $this->login, 'type' => $this->type];
    }
}
