<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of registering an in-process Auto mode switch handler.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionUiRegisterDirectAutoModeSwitchHandlerResult implements Arrayable
{
    public function __construct(
        public string $handle,
        public ?bool $autoModeRecovery = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            handle: Arr::string($data, 'handle'),
            autoModeRecovery: isset($data['autoModeRecovery']) ? (bool) $data['autoModeRecovery'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'handle' => $this->handle,
            'autoModeRecovery' => $this->autoModeRecovery,
        ], fn ($value): bool => $value !== null);
    }
}
