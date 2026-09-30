<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Acknowledgement that the requested environment was deleted.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnvironmentsDeleteResult implements Arrayable
{
    public static function fromArray(array $data = []): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
