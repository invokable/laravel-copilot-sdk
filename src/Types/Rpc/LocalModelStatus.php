<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Effective file or download state.
 *
 * @experimental
 */
readonly class LocalModelStatus implements Arrayable
{
    public function __construct(
        public string $kind,
        public ?int $sizeBytes = null,
        public ?bool $updateAvailable = null,
        public ?int $receivedBytes = null,
        public ?int $totalBytes = null,
        public ?string $stage = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'] ?? '',
            sizeBytes: isset($data['sizeBytes']) ? (int) $data['sizeBytes'] : null,
            updateAvailable: $data['updateAvailable'] ?? null,
            receivedBytes: isset($data['receivedBytes']) ? (int) $data['receivedBytes'] : null,
            totalBytes: isset($data['totalBytes']) ? (int) $data['totalBytes'] : null,
            stage: $data['stage'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            ...array_filter([
                'sizeBytes' => $this->sizeBytes,
                'updateAvailable' => $this->updateAvailable,
                'receivedBytes' => $this->receivedBytes,
                'totalBytes' => $this->totalBytes,
                'stage' => $this->stage,
            ], static fn ($value) => $value !== null),
        ];
    }
}
