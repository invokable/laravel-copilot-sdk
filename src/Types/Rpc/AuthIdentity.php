<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AuthInfoType;

/**
 * Credential-free authentication identity.
 */
readonly class AuthIdentity implements Arrayable
{
    /**
     * @param  AuthInfoType|string  $type  Authentication type.
     * @param  string  $host  Authentication host.
     * @param  string|null  $login  Authenticated login, when available.
     * @param  string|null  $envVar  Environment variable that supplied the credential, when applicable.
     * @param  string|null  $registrationId  Opaque credential registration identifier.
     * @param  array<mixed>|null  $copilotUser  Snapshot of the authenticated user's Copilot subscription, when known.
     */
    public function __construct(
        public AuthInfoType|string $type,
        public string $host,
        public ?string $login = null,
        public ?string $envVar = null,
        public ?string $registrationId = null,
        public ?array $copilotUser = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? '';

        return new self(
            type: is_string($type) ? (AuthInfoType::tryFrom($type) ?? $type) : $type,
            host: Arr::string($data, 'host'),
            login: $data['login'] ?? null,
            envVar: $data['envVar'] ?? null,
            registrationId: $data['registrationId'] ?? null,
            copilotUser: $data['copilotUser'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type instanceof AuthInfoType ? $this->type->value : $this->type,
            'host' => $this->host,
            'login' => $this->login,
            'envVar' => $this->envVar,
            'registrationId' => $this->registrationId,
            'copilotUser' => $this->copilotUser,
        ], fn ($value) => $value !== null);
    }
}
