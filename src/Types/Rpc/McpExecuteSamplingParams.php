<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for executing an MCP sampling request.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpExecuteSamplingParams implements Arrayable
{
    /**
     * @param  array<string, mixed>  $request  Raw MCP CreateMessageRequest parameters.
     * @param  mixed  $mcpRequestId  Original MCP JSON-RPC request ID as a JSON value.
     */
    public function __construct(
        public string $requestId,
        public string $serverName,
        public mixed $mcpRequestId,
        public array $request,
    ) {}

    public static function fromArray(array $data): self
    {
        if (! array_key_exists('mcpRequestId', $data)) {
            throw new \InvalidArgumentException('The mcpRequestId field is required.');
        }

        return new self(
            requestId: Arr::string($data, 'requestId'),
            serverName: Arr::string($data, 'serverName'),
            mcpRequestId: $data['mcpRequestId'],
            request: Arr::array($data, 'request'),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'serverName' => $this->serverName,
            'mcpRequestId' => $this->mcpRequestId,
            'request' => $this->request,
        ];
    }
}
