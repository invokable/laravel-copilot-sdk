<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Whether URL permissions should run in unrestricted mode.
 */
readonly class PermissionUrlsSetUnrestrictedModeParams implements Arrayable
{
    public function __construct(
        public bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(enabled: $data['enabled'] ?? false);
    }

    public function toArray(): array
    {
        return ['enabled' => $this->enabled];
    }
}
