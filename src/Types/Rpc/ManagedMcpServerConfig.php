<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Non-secret host-managed HTTP MCP server configuration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ManagedMcpServerConfig implements Arrayable
{
    /**
     * @param  string[]|null  $tools
     */
    public function __construct(
        public string $displayName,
        public string $url,
        public ?array $tools = null,
        public ?int $timeout = null,
        public ?int $headersRefreshTtlMs = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            displayName: Arr::string($data, 'displayName'),
            url: Arr::string($data, 'url'),
            tools: $data['tools'] ?? null,
            timeout: isset($data['timeout']) ? Arr::integer($data, 'timeout') : null,
            headersRefreshTtlMs: isset($data['headersRefreshTtlMs']) ? Arr::integer($data, 'headersRefreshTtlMs') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'displayName' => $this->displayName,
            'url' => $this->url,
            'tools' => $this->tools,
            'timeout' => $this->timeout,
            'headersRefreshTtlMs' => $this->headersRefreshTtlMs,
        ], fn ($value) => $value !== null);
    }
}
