<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Pending MCP OAuth request ID and host-provided token or cancellation response.
 */
readonly class McpOauthHandlePendingRequest implements Arrayable
{
    /**
     * @param  string  $requestId  OAuth request identifier from the mcp.oauth_required event.
     * @param  McpOauthPendingRequestResponse|array  $result  Host response to the pending OAuth request.
     */
    public function __construct(
        public string $requestId,
        public McpOauthPendingRequestResponse|array $result,
    ) {}

    public static function fromArray(array $data): self
    {
        $result = $data['result'] instanceof McpOauthPendingRequestResponse
            ? $data['result']
            : McpOauthPendingRequestResponse::fromArray($data['result']);

        return new self(
            requestId: Arr::string($data, 'requestId'),
            result: $result,
        );
    }

    public function toArray(): array
    {
        $result = $this->result instanceof McpOauthPendingRequestResponse
            ? $this->result->toArray()
            : $this->result;

        return [
            'requestId' => $this->requestId,
            'result' => $result,
        ];
    }
}
