<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ManagedSettingsRecheckPolicyHelperRequest implements Arrayable
{
    public static function fromArray(array $data): static
    {
        return new static;
    }

    public function toArray(): array
    {
        return [];
    }
}
