<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AuthLoginResultStatus;

/** @experimental */
readonly class AuthLoginResultDto implements Arrayable
{
    public function __construct(
        public AuthLoginResultStatus|string $status,
        public ?string $host = null,
        public ?string $login = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: AuthLoginResultStatus::tryFrom((string) ($data['status'] ?? '')) ?? (string) ($data['status'] ?? ''),
            host: isset($data['host']) ? (string) $data['host'] : null,
            login: isset($data['login']) ? (string) $data['login'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status instanceof AuthLoginResultStatus ? $this->status->value : $this->status,
            'host' => $this->host,
            'login' => $this->login,
        ], static fn ($value): bool => $value !== null);
    }
}
