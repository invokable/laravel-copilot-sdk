<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Completed and verified Azure ML model files.
 *
 * @experimental
 */
readonly class AzureMlDownloadResult implements Arrayable
{
    /**
     * @param  array<string>  $extraFiles
     */
    public function __construct(
        public string $primaryPath,
        public string $manifestPath,
        public int $totalBytes,
        public bool $reused,
        public mixed $serveOptions,
        public int $maxConcurrent,
        public array $extraFiles,
        public ?int $totalFailures = null,
        public ?int $receivedBytes = null,
        public ?bool $resumed = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            primaryPath: $data['primaryPath'],
            manifestPath: $data['manifestPath'],
            totalBytes: (int) $data['totalBytes'],
            reused: $data['reused'],
            serveOptions: $data['serveOptions'],
            maxConcurrent: (int) $data['maxConcurrent'],
            extraFiles: $data['extraFiles'],
            totalFailures: isset($data['totalFailures']) ? (int) $data['totalFailures'] : null,
            receivedBytes: isset($data['receivedBytes']) ? (int) $data['receivedBytes'] : null,
            resumed: $data['resumed'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'primaryPath' => $this->primaryPath,
            'manifestPath' => $this->manifestPath,
            'totalBytes' => $this->totalBytes,
            'reused' => $this->reused,
            'serveOptions' => $this->serveOptions,
            'maxConcurrent' => $this->maxConcurrent,
            'extraFiles' => $this->extraFiles,
            ...array_filter([
                'totalFailures' => $this->totalFailures,
                'receivedBytes' => $this->receivedBytes,
                'resumed' => $this->resumed,
            ], fn ($value) => $value !== null),
        ];
    }
}
