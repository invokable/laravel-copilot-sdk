<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A settings value with its secret-bearing parts replaced by a redacted placeholder.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SettingsRedactValueForDisplayResult implements Arrayable
{
    public function __construct(
        public mixed $value,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'] ?? null,
        );
    }

    public function toArray(): array
    {
        return ['value' => $this->value];
    }
}
