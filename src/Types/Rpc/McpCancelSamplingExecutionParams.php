<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to cancel an in-flight MCP sampling execution.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpCancelSamplingExecutionParams implements Arrayable
{
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
