<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionsAllowAllMode;

/**
 * Indicates whether the operation succeeded and reports the post-mutation state.
 *
 * @experimental
 */
readonly class AllowAllPermissionSetResult implements Arrayable
{
    /**
     * @param  bool  $enabled  Authoritative full allow-all state after the mutation.
     * @param  bool  $success  Whether the operation succeeded.
     * @param  PermissionsAllowAllMode|null  $mode  Authoritative allow-all mode after the mutation.
     */
    public function __construct(
        public bool $enabled,
        public bool $success,
        public ?PermissionsAllowAllMode $mode = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            enabled: Arr::boolean($data, 'enabled'),
            success: Arr::boolean($data, 'success'),
            mode: isset($data['mode']) ? PermissionsAllowAllMode::tryFrom($data['mode']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'success' => $this->success,
            'mode' => $this->mode?->value,
        ], fn ($value) => $value !== null);
    }
}
