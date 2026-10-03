<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AuthInfoType;

/** Credential-free account identity metadata. */
readonly class AuthIdentityMetadata implements Arrayable
{
    public function __construct(
        public AuthInfoType|string $type,
        public string $host,
        public string $login,
    ) {}

    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? '';

        return new self(
            type: is_string($type) ? (AuthInfoType::tryFrom($type) ?? $type) : $type,
            host: (string) ($data['host'] ?? ''),
            login: (string) ($data['login'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type instanceof AuthInfoType ? $this->type->value : $this->type,
            'host' => $this->host,
            'login' => $this->login,
        ];
    }
}
