<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Token credentials accepted by session.gitHubAuth.setCredentials.
 */
readonly class SettableTokenAuthInfo implements Arrayable
{
    public function __construct(
        public string $host,
        public string $token,
        public ?array $copilotUser = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            host: (string) ($data['host'] ?? ''),
            token: (string) ($data['token'] ?? ''),
            copilotUser: $data['copilotUser'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => 'token',
            'host' => $this->host,
            'token' => $this->token,
            'copilotUser' => $this->copilotUser,
        ], fn ($value) => $value !== null);
    }
}
