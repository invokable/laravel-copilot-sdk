<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\SessionListFilter;

/**
 * Options for listing persisted sessions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsListRequest implements Arrayable
{
    public function __construct(
        public ?string $source = null,
        public ?int $metadataLimit = null,
        public SessionListFilter|array|null $filter = null,
        public ?bool $includeDetached = null,
        public ?bool $throwOnError = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $filter = $data['filter'] ?? null;
        if (is_array($filter)) {
            $filter = SessionListFilter::fromArray($filter);
        }

        return new self(
            source: $data['source'] ?? null,
            metadataLimit: $data['metadataLimit'] ?? null,
            filter: $filter,
            includeDetached: $data['includeDetached'] ?? null,
            throwOnError: $data['throwOnError'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'source' => $this->source,
            'metadataLimit' => $this->metadataLimit,
            'filter' => $this->filter instanceof SessionListFilter ? $this->filter->toArray() : $this->filter,
            'includeDetached' => $this->includeDetached,
            'throwOnError' => $this->throwOnError,
        ], fn ($value): bool => $value !== null);
    }
}
