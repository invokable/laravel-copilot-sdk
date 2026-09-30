<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Identify a Mission Control environment to retrieve / delete by ID.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnvironmentsGetRequest implements Arrayable
{
    public function __construct(
        public string $environmentId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(environmentId: Arr::string($data, 'environmentId', ''));
    }

    public function toArray(): array
    {
        return ['environmentId' => $this->environmentId];
    }
}
