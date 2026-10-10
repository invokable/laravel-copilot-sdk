<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Runtime-owned policy snapshot used for pure managed-permission evaluation. */
readonly class ManagedPermissionsContext implements Arrayable
{
    public function __construct(
        public bool $failClosed,
        public mixed $permissions = null,
        private bool $permissionsProvided = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            failClosed: (bool) ($data['failClosed'] ?? false),
            permissions: $data['permissions'] ?? null,
            permissionsProvided: array_key_exists('permissions', $data),
        );
    }

    public function toArray(): array
    {
        $data = ['failClosed' => $this->failClosed];

        if ($this->permissionsProvided || $this->permissions !== null) {
            $data['permissions'] = $this->permissions;
        }

        return $data;
    }
}
