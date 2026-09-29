<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AuthLoginBegun implements Arrayable
{
    public function __construct(public string $flowId, public AuthLoginStep $step) {}

    public static function fromArray(array $data): self
    {
        return new self($data['flowId'] ?? '', AuthLoginStep::fromArray($data['step'] ?? []));
    }

    public function toArray(): array
    {
        return ['flowId' => $this->flowId, 'step' => $this->step->toArray()];
    }
}
