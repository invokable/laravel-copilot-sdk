<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionsAllowAllMode;

/**
 * Current allow-all permission mode.
 *
 * @experimental
 */
readonly class AllowAllPermissionState implements Arrayable
{
    /**
     * @param  bool  $enabled  Whether full allow-all permissions are currently active.
     * @param  PermissionsAllowAllMode|null  $mode  Current allow-all mode.
     */
    public function __construct(
        public bool $enabled,
        public ?PermissionsAllowAllMode $mode = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            enabled: Arr::boolean($data, 'enabled'),
            mode: isset($data['mode']) ? PermissionsAllowAllMode::tryFrom($data['mode']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'mode' => $this->mode?->value,
        ], fn ($value) => $value !== null);
    }
}
