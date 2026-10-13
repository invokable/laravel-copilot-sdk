<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Delete persisted sessions by ID.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsBulkDeleteRequest implements Arrayable
{
    /**
     * @param  array<string>  $sessionIds
     */
    public function __construct(
        public array $sessionIds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(sessionIds: $data['sessionIds'] ?? []);
    }

    public function toArray(): array
    {
        return ['sessionIds' => $this->sessionIds];
    }
}
