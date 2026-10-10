<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request ID for one cancellable MCP registry search.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
readonly class McpRegistryRequestIdResult implements Arrayable
{
    public function __construct(
        public int $requestId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::integer($data, 'requestId'),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
        ];
    }
}
