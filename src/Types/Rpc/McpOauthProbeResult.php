<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of a passive MCP OAuth probe.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpOauthProbeResult implements Arrayable
{
    /**
     * @param  array<string, mixed>|null  $httpResponse
     * @param  array<string, mixed>|null  $wwwAuthenticateParams
     */
    public function __construct(
        public string $status,
        public ?array $httpResponse = null,
        public ?string $reason = null,
        public ?array $wwwAuthenticateParams = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: Arr::string($data, 'status'),
            httpResponse: $data['httpResponse'] ?? null,
            reason: $data['reason'] ?? null,
            wwwAuthenticateParams: $data['wwwAuthenticateParams'] ?? null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'httpResponse' => $this->httpResponse,
            'reason' => $this->reason,
            'wwwAuthenticateParams' => $this->wwwAuthenticateParams,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
