<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionMode;

/**
 * Indicates whether the requested permission mode was applied and reports the
 * authoritative post-mutation mode.
 *
 * @experimental
 */
readonly class PermissionsSetModeResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the operation succeeded.
     * @param  PermissionMode  $mode  Authoritative permission mode after the mutation.
     */
    public function __construct(
        public bool $success,
        public PermissionMode $mode,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            success: Arr::boolean($data, 'success'),
            mode: PermissionMode::from($data['mode']),
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'mode' => $this->mode->value,
        ];
    }
}
