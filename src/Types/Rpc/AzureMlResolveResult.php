<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Resolved Azure ML artifact facts without signed download URLs.
 *
 * @experimental
 */
readonly class AzureMlResolveResult implements Arrayable
{
    /**
     * @param  array<string>  $extraFiles
     */
    public function __construct(
        public int $totalBytes,
        public string $primaryPath,
        public array $extraFiles,
        public mixed $serveOptions,
        public int $maxConcurrent,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalBytes: (int) $data['totalBytes'],
            primaryPath: $data['primaryPath'],
            extraFiles: $data['extraFiles'],
            serveOptions: $data['serveOptions'],
            maxConcurrent: (int) $data['maxConcurrent'],
        );
    }

    public function toArray(): array
    {
        return [
            'totalBytes' => $this->totalBytes,
            'primaryPath' => $this->primaryPath,
            'extraFiles' => $this->extraFiles,
            'serveOptions' => $this->serveOptions,
            'maxConcurrent' => $this->maxConcurrent,
        ];
    }
}
