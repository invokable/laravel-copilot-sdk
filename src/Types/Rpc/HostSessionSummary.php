<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Metadata advertised by an Agent Host Protocol host. */
readonly class HostSessionSummary implements Arrayable
{
    public function __construct(
        public string $resource,
        public string $title,
        public string $createdAt,
        public string $modifiedAt,
        public int $status,
        public ?string $activity = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resource: $data['resource'] ?? '',
            title: $data['title'] ?? '',
            createdAt: $data['createdAt'] ?? '',
            modifiedAt: $data['modifiedAt'] ?? '',
            status: (int) ($data['status'] ?? 0),
            activity: $data['activity'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'resource' => $this->resource,
            'title' => $this->title,
            'createdAt' => $this->createdAt,
            'modifiedAt' => $this->modifiedAt,
            'status' => $this->status,
            'activity' => $this->activity,
        ], fn ($value) => $value !== null);
    }
}
