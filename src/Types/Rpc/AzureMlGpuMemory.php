<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Graphics memory available for an on-device model.
 *
 * @experimental
 */
readonly class AzureMlGpuMemory implements Arrayable
{
    public function __construct(
        public int $totalBytes,
        public int $dedicatedBytes,
        public int $sharedBytes,
        public string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalBytes: (int) ($data['totalBytes'] ?? 0),
            dedicatedBytes: (int) ($data['dedicatedBytes'] ?? 0),
            sharedBytes: (int) ($data['sharedBytes'] ?? 0),
            description: $data['description'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'totalBytes' => $this->totalBytes,
            'dedicatedBytes' => $this->dedicatedBytes,
            'sharedBytes' => $this->sharedBytes,
            'description' => $this->description,
        ];
    }
}
