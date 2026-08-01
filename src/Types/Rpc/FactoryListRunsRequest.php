<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for listing factory runs. Currently takes no arguments.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryListRunsRequest implements Arrayable
{
    public function __construct() {}

    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
