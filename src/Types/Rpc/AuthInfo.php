<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AuthInfoType;

/**
 * Authentication credentials for session.auth.setCredentials.
 */
readonly class AuthInfo implements Arrayable
{
    /**
     * @param  string  $host  Authentication host.
     * @param  AuthInfoType|string  $type  Authentication type.
     * @param  array<mixed>|null  $copilotUser  Optional Copilot user response snapshot.
     * @param  string|null  $hmac  HMAC secret used to sign requests.
     * @param  string|null  $envVar  Name of the environment variable the token was sourced from.
     * @param  string|null  $login  Authenticated login.
     * @param  string|null  $token  Token value.
     * @param  string|null  $apiKey  API key value.
     */
    public function __construct(
        public string $host,
        public AuthInfoType|string $type,
        public ?array $copilotUser = null,
        public ?string $hmac = null,
        public ?string $envVar = null,
        public ?string $login = null,
        public ?string $token = null,
        public ?string $apiKey = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = AuthInfoType::tryFrom($data['type']) ?? $data['type'];

        return new self(
            host: Arr::string($data, 'host'),
            type: $type,
            copilotUser: $data['copilotUser'] ?? null,
            hmac: $data['hmac'] ?? null,
            envVar: $data['envVar'] ?? null,
            login: $data['login'] ?? null,
            token: $data['token'] ?? null,
            apiKey: $data['apiKey'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'host' => $this->host,
            'type' => $this->type instanceof AuthInfoType ? $this->type->value : $this->type,
            'copilotUser' => $this->copilotUser,
            'hmac' => $this->hmac,
            'envVar' => $this->envVar,
            'login' => $this->login,
            'token' => $this->token,
            'apiKey' => $this->apiKey,
        ], fn ($value) => $value !== null);
    }
}
