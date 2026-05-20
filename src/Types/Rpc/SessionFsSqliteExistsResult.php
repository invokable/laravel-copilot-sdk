<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Indicates whether the per-session SQLite database already exists.
 */
readonly class SessionFsSqliteExistsResult implements Arrayable
{
    /**
     * @param  bool  $exists  Whether the session database already exists
     */
    public function __construct(
        public bool $exists,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            exists: (bool) ($data['exists'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'exists' => $this->exists,
        ];
    }
}
