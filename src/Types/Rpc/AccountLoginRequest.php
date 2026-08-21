<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Credentials to validate and store. Omit login to resolve the authenticated user from the token.
 */
readonly class AccountLoginRequest implements Arrayable
{
    /**
     * @param  string  $host  GitHub host URL.
     * @param  string  $token  GitHub authentication token.
     * @param  string|null  $login  User login/username. When omitted, the runtime validates the token
     *                              and resolves the login from GitHub.
     */
    public function __construct(
        public string $host,
        public string $token,
        public ?string $login = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            host: Arr::string($data, 'host', ''),
            token: Arr::string($data, 'token', ''),
            login: Arr::get($data, 'login'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'host' => $this->host,
            'login' => $this->login,
            'token' => $this->token,
        ], fn ($value) => $value !== null);
    }
}
