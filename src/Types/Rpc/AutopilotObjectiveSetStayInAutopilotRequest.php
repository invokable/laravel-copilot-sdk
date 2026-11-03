<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class AutopilotObjectiveSetStayInAutopilotRequest implements Arrayable
{
    public function __construct(
        public bool $value,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: Arr::boolean($data, 'value'),
        );
    }

    public function toArray(): array
    {
        return ['value' => $this->value];
    }
}
