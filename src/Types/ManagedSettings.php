<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Host-injected enterprise managed settings for a session.
 */
readonly class ManagedSettings implements Arrayable
{
    public function __construct(
        public ManagedSettingsPermissions|array|null $permissions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $permissions = $data['permissions'] ?? null;

        return new self(
            permissions: $permissions instanceof ManagedSettingsPermissions
                ? $permissions
                : ($permissions !== null ? ManagedSettingsPermissions::fromArray($permissions) : null),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'permissions' => $this->permissions instanceof ManagedSettingsPermissions
                ? $this->permissions->toArray()
                : $this->permissions,
        ], fn ($value) => $value !== null);
    }
}
