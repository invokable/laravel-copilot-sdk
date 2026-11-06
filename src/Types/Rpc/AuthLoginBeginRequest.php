<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AuthLoginMethod;
use Revolution\Copilot\Enums\AuthLoginMode;
use Revolution\Copilot\Enums\LoginProviderKind;

readonly class AuthLoginBeginRequest implements Arrayable
{
    public function __construct(
        public LoginProviderKind|string $kind,
        public AuthLoginMode|string|null $mode = null,
        public ?string $accountId = null,
        public AuthLoginMethod|string|null $method = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind instanceof LoginProviderKind ? $this->kind->value : $this->kind,
            'mode' => $this->mode instanceof AuthLoginMode ? $this->mode->value : $this->mode,
            'accountId' => $this->accountId,
            'method' => $this->method instanceof AuthLoginMethod ? $this->method->value : $this->method,
        ], static fn ($value) => $value !== null);
    }
}
