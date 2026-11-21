<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class AccountsSwitchActiveRequest implements Arrayable
{
    public function __construct(public string $selectionId) {}

    public static function fromArray(array $data): self
    {
        return new self(selectionId: $data['selectionId'] ?? '');
    }

    public function toArray(): array
    {
        return ['selectionId' => $this->selectionId];
    }
}
