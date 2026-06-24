<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Logout result indicating if more users remain.
 */
readonly class AccountLogoutResult implements Arrayable
{
    /**
     * @param  bool  $hasMoreUsers  Whether other authenticated users remain after logout.
     */
    public function __construct(
        public bool $hasMoreUsers,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hasMoreUsers: $data['hasMoreUsers'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'hasMoreUsers' => $this->hasMoreUsers,
        ];
    }
}
