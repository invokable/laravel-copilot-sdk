<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Host context to set for MCP App guests.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpAppsSetHostContextRequest implements Arrayable
{
    public function __construct(
        public McpAppsSetHostContextDetails $context,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            context: McpAppsSetHostContextDetails::fromArray(Arr::array($data, 'context')),
        );
    }

    public function toArray(): array
    {
        return [
            'context' => $this->context->toArray(),
        ];
    }
}
