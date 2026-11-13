<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to resolve sandbox egress for a remote MCP endpoint.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionSandboxGetRemoteMcpEgressRequest implements Arrayable
{
    public function __construct(
        public ?string $secretConfigDir = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            secretConfigDir: isset($data['secretConfigDir']) ? Arr::string($data, 'secretConfigDir') : null,
            url: isset($data['url']) ? Arr::string($data, 'url') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'secretConfigDir' => $this->secretConfigDir,
            'url' => $this->url,
        ], static fn ($value): bool => $value !== null);
    }
}
