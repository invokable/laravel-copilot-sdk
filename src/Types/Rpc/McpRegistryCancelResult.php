<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether cancellation stopped a running MCP registry search.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
readonly class McpRegistryCancelResult implements Arrayable
{
    public function __construct(
        public bool $canceled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            canceled: Arr::boolean($data, 'canceled', false),
        );
    }

    public function toArray(): array
    {
        return [
            'canceled' => $this->canceled,
        ];
    }
}
