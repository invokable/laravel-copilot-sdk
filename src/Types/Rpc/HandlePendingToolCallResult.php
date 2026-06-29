<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of handling a pending tool call.
 */
readonly class HandlePendingToolCallResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the tool call was handled successfully
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
