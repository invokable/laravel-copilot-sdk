<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Whether the name produces a nonempty filename. */
readonly class AgentsValidateNameResult implements Arrayable
{
    public function __construct(public bool $valid) {}

    public static function fromArray(array $data): self
    {
        return new self(valid: Arr::boolean($data, 'valid'));
    }

    public function toArray(): array
    {
        return ['valid' => $this->valid];
    }
}
