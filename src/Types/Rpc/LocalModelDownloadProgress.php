<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Current model download progress.
 *
 * @experimental
 */
readonly class LocalModelDownloadProgress implements Arrayable
{
    public function __construct(
        public int $receivedBytes,
        public ?int $totalBytes = null,
        public ?string $stage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            receivedBytes: (int) ($data['receivedBytes'] ?? 0),
            totalBytes: isset($data['totalBytes']) ? (int) $data['totalBytes'] : null,
            stage: $data['stage'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'receivedBytes' => $this->receivedBytes,
            ...array_filter([
                'totalBytes' => $this->totalBytes,
                'stage' => $this->stage,
            ], static fn ($value) => $value !== null),
        ];
    }
}
