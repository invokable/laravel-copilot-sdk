<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Memory approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsMemory implements Arrayable
{
    public string $kind;

    public function __construct()
    {
        $this->kind = 'memory';
    }

    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return ['kind' => $this->kind];
    }
}
