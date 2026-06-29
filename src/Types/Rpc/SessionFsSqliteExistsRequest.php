<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Identifies the target session for a SQLite exists check.
 */
readonly class SessionFsSqliteExistsRequest implements Arrayable
{
    /**
     * @param  string  $sessionId  Target session identifier
     */
    public function __construct(
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
        ];
    }
}
