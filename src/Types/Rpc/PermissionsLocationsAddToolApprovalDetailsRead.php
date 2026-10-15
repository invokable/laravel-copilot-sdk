<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Read approval stored for a permissions location.
 */
readonly class PermissionsLocationsAddToolApprovalDetailsRead implements Arrayable
{
    public string $kind;

    public function __construct()
    {
        $this->kind = 'read';
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
