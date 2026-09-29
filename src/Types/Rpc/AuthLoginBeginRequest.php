<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\LoginProviderKind;

readonly class AuthLoginBeginRequest implements Arrayable
{
    public function __construct(public LoginProviderKind|string $kind) {}

    public function toArray(): array
    {
        return ['kind' => $this->kind instanceof LoginProviderKind ? $this->kind->value : $this->kind];
    }
}
