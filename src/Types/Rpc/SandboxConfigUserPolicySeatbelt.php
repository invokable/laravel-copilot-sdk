<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * macOS seatbelt-specific sandbox policy options.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxConfigUserPolicySeatbelt implements Arrayable
{
    /**
     * @param  ?bool  $keychainAccess  Whether the macOS seatbelt profile may access the keychain.
     */
    public function __construct(
        public ?bool $keychainAccess = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            keychainAccess: $data['keychainAccess'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'keychainAccess' => $this->keychainAccess,
        ], fn ($v) => $v !== null);
    }
}
