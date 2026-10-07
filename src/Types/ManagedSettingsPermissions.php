<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Permissions-only managed policy injected by the host.
 */
readonly class ManagedSettingsPermissions implements Arrayable
{
    public function __construct(
        public ?string $disableBypassPermissionsMode = null,
        public ?array $deny = null,
        public ?array $ask = null,
        public ?array $allow = null,
        public ?bool $disableAssistedPermissionsMode = null,
        public ?array $limitTo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            disableAssistedPermissionsMode: $data['disableAssistedPermissionsMode'] ?? null,
            disableBypassPermissionsMode: $data['disableBypassPermissionsMode'] ?? null,
            deny: $data['deny'] ?? null,
            ask: $data['ask'] ?? null,
            allow: $data['allow'] ?? null,
            limitTo: $data['limitTo'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'disableBypassPermissionsMode' => $this->disableBypassPermissionsMode,
            'deny' => $this->deny,
            'ask' => $this->ask,
            'allow' => $this->allow,
            'disableAssistedPermissionsMode' => $this->disableAssistedPermissionsMode,
            'limitTo' => $this->limitTo,
        ], fn ($value) => $value !== null);
    }
}
