<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Host response to a pending MCP dynamic headers refresh request.
 *
 * Either provides headers to overlay or indicates none are available.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpHeadersHandlePendingHeadersRefreshRequest implements Arrayable
{
    /**
     * @param  string  $kind  Either "headers" (with headers array) or "none".
     * @param  array<string>|null  $headers  Headers to overlay onto the MCP request (only when kind is "headers").
     */
    public function __construct(
        public string $kind,
        public ?array $headers = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: (string) ($data['kind'] ?? 'none'),
            headers: isset($data['headers']) ? (array) $data['headers'] : null,
        );
    }

    public function toArray(): array
    {
        $result = ['kind' => $this->kind];

        if ($this->headers !== null) {
            $result['headers'] = $this->headers;
        }

        return $result;
    }
}
