<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Per-key metadata for a known user setting.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UserSettingMetadata implements Arrayable
{
    /**
     * @param  array  $value    The effective value: the user's value if set, otherwise the default.
     * @param  array  $default  The centrally-known default for this setting (null when no default is registered).
     * @param  bool   $isDefault  True when the user has not set an explicit value for this setting.
     */
    public function __construct(
        public array $value,
        public array $default,
        public bool $isDefault,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'] ?? [],
            default: $data['default'] ?? [],
            isDefault: $data['isDefault'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'default' => $this->default,
            'isDefault' => $this->isDefault,
        ];
    }
}
