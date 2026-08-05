<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of clearing the session conversation context.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistoryClearContextResult implements Arrayable
{
    public function __construct(
        public int $messagesCleared,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(messagesCleared: Arr::integer($data, 'messagesCleared'));
    }

    public function toArray(): array
    {
        return ['messagesCleared' => $this->messagesCleared];
    }
}
