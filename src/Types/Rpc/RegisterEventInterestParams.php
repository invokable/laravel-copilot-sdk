<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for registering runtime interest in an event type.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RegisterEventInterestParams implements Arrayable
{
    public function __construct(
        public string $eventType,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventType: Arr::string($data, 'eventType', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'eventType' => $this->eventType,
        ];
    }
}
