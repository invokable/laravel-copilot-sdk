<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for the heaviest-messages query.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataContextHeaviestMessagesRequest implements Arrayable
{
    /**
     * @param  ?int  $limit  Maximum number of messages to return, most-expensive first. Omit for the server default.
     */
    public function __construct(
        public ?int $limit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            limit: $data['limit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'limit' => $this->limit,
        ], fn ($value): bool => $value !== null);
    }
}
