<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Sandbox egress decision for a remote MCP endpoint.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionSandboxGetRemoteMcpEgressResult implements Arrayable
{
    public function __construct(
        public ?string $deniedReason = null,
        public ?string $proxyUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            deniedReason: isset($data['deniedReason']) ? Arr::string($data, 'deniedReason') : null,
            proxyUrl: isset($data['proxyUrl']) ? Arr::string($data, 'proxyUrl') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'deniedReason' => $this->deniedReason,
            'proxyUrl' => $this->proxyUrl,
        ], static fn ($value): bool => $value !== null);
    }
}
