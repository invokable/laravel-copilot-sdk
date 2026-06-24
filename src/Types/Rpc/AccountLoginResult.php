<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of a successful login; throws on failure.
 */
readonly class AccountLoginResult implements Arrayable
{
    /**
     * @param  bool  $storedInVault  Whether the credential was persisted to a secure store (system keychain, or the config
     *                               file when plaintext storage is enabled). False when no secure store was available and
     *                               the token was not saved.
     */
    public function __construct(
        public bool $storedInVault,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            storedInVault: $data['storedInVault'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'storedInVault' => $this->storedInVault,
        ];
    }
}
