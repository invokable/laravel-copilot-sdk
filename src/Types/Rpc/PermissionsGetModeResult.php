<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\PermissionMode;

/**
 * Current permission mode.
 *
 * @experimental
 */
readonly class PermissionsGetModeResult implements Arrayable
{
    /**
     * @param  PermissionMode  $mode  Current permission mode.
     */
    public function __construct(
        public PermissionMode $mode,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            mode: PermissionMode::from($data['mode']),
        );
    }

    public function toArray(): array
    {
        return [
            'mode' => $this->mode->value,
        ];
    }
}
