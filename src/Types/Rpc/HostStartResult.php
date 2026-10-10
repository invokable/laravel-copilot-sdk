<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Bound listener details returned by host.start. */
readonly class HostStartResult implements Arrayable
{
    public function __construct(
        public string $hostId,
        public ?string $url = null,
        public ?string $token = null,
        public ?string $environmentId = null,
        public ?int $pid = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            url: $data['url'] ?? null,
            token: $data['token'] ?? null,
            environmentId: $data['environmentId'] ?? null,
            pid: isset($data['pid']) ? (int) $data['pid'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hostId' => $this->hostId,
            'url' => $this->url,
            'token' => $this->token,
            'environmentId' => $this->environmentId,
            'pid' => $this->pid,
        ], fn ($value) => $value !== null);
    }
}
