<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Credentials to store after successful authentication.
 */
readonly class AccountLoginRequest implements Arrayable
{
    /**
     * @param  string  $host  GitHub host URL.
     * @param  string  $login  User login/username.
     * @param  string  $token  GitHub authentication token.
     */
    public function __construct(
        public string $host,
        public string $login,
        public string $token,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            host: $data['host'] ?? '',
            login: $data['login'] ?? '',
            token: $data['token'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'login' => $this->login,
            'token' => $this->token,
        ];
    }
}
