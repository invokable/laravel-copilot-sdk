<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Experimental macOS seatbelt policy options.
 *
 * @experimental
 */
readonly class SandboxConfigUserPolicyExperimentalSeatbelt implements Arrayable
{
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
        ], fn ($value) => $value !== null);
    }
}
