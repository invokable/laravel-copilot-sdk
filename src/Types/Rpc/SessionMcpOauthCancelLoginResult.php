<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether an MCP OAuth login was cancelled.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionMcpOauthCancelLoginResult implements Arrayable
{
    public function __construct(
        public bool $cancelled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cancelled: Arr::boolean($data, 'cancelled'),
        );
    }

    public function toArray(): array
    {
        return [
            'cancelled' => $this->cancelled,
        ];
    }
}
