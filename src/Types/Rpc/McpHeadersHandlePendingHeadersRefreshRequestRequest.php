<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters to respond to a pending MCP dynamic headers refresh request.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpHeadersHandlePendingHeadersRefreshRequestRequest implements Arrayable
{
    /**
     * @param  string                                          $requestId  Headers refresh request identifier from mcp.headers_refresh_required.
     * @param  McpHeadersHandlePendingHeadersRefreshRequest   $result     The host response (headers or none).
     */
    public function __construct(
        public string $requestId,
        public McpHeadersHandlePendingHeadersRefreshRequest $result,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: (string) ($data['requestId'] ?? ''),
            result: McpHeadersHandlePendingHeadersRefreshRequest::fromArray((array) ($data['result'] ?? [])),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'result' => $this->result->toArray(),
        ];
    }
}
