<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Progress and retry facts for one active Azure ML download.
 *
 * @experimental
 */
readonly class AzureMlDownloadStatus implements Arrayable
{
    public function __construct(
        public string $phase,
        public int $completedBytes,
        public int $totalBytes,
        public ?int $retryAttempt = null,
        public ?int $maxAttempts = null,
        public ?string $retryReason = null,
        public ?int $retryDelayMs = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            phase: $data['phase'],
            completedBytes: (int) $data['completedBytes'],
            totalBytes: (int) $data['totalBytes'],
            retryAttempt: isset($data['retryAttempt']) ? (int) $data['retryAttempt'] : null,
            maxAttempts: isset($data['maxAttempts']) ? (int) $data['maxAttempts'] : null,
            retryReason: $data['retryReason'] ?? null,
            retryDelayMs: isset($data['retryDelayMs']) ? (int) $data['retryDelayMs'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'phase' => $this->phase,
            'completedBytes' => $this->completedBytes,
            'totalBytes' => $this->totalBytes,
            ...array_filter([
                'retryAttempt' => $this->retryAttempt,
                'maxAttempts' => $this->maxAttempts,
                'retryReason' => $this->retryReason,
                'retryDelayMs' => $this->retryDelayMs,
            ], fn ($value) => $value !== null),
        ];
    }
}
