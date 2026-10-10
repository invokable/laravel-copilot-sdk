<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Local WebSocket host options. */
readonly class HostLocalServerOptions implements Arrayable
{
    public function __construct(
        public ?string $hostname = null,
        public ?int $port = null,
        public ?string $token = null,
        public ?bool $requireConnectionToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostname: $data['hostname'] ?? null,
            port: isset($data['port']) ? (int) $data['port'] : null,
            token: $data['token'] ?? null,
            requireConnectionToken: $data['requireConnectionToken'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hostname' => $this->hostname,
            'port' => $this->port,
            'token' => $this->token,
            'requireConnectionToken' => $this->requireConnectionToken,
        ], fn ($value) => $value !== null);
    }
}
