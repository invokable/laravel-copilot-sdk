<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether the pending MCP OAuth response was accepted.
 */
readonly class McpOauthHandlePendingResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the response was accepted. False if the request was unknown, timed out, or already resolved.
     */
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: Arr::boolean($data, 'success'),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
