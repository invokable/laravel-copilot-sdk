<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AccountsSetCredentialsRequest implements Arrayable
{
    public function __construct(public string $host, public string $login, public string $token) {}

    public static function fromArray(array $data): self
    {
        return new self(host: $data['host'] ?? '', login: $data['login'] ?? '', token: $data['token'] ?? '');
    }

    public function toArray(): array
    {
        return ['host' => $this->host, 'login' => $this->login, 'token' => $this->token];
    }
}
