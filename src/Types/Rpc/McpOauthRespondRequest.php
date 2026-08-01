<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Pending MCP OAuth request id to respond to.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpOauthRespondRequest implements Arrayable
{
    /**
     * @param  string  $requestId  OAuth request identifier from the mcp.oauth_required event.
     */
    public function __construct(
        public string $requestId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId'),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
        ];
    }
}
