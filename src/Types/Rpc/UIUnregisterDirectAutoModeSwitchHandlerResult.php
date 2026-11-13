<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of unregistering an in-process Auto mode switch handler.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UIUnregisterDirectAutoModeSwitchHandlerResult implements Arrayable
{
    public function __construct(
        public bool $unregistered,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            unregistered: Arr::boolean($data, 'unregistered'),
        );
    }

    public function toArray(): array
    {
        return [
            'unregistered' => $this->unregistered,
        ];
    }
}
