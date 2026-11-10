<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Measured graphics capability; missing memory means the adapter is unknown.
 *
 * @experimental
 */
readonly class AzureMlHardwareResult implements Arrayable
{
    public function __construct(
        public ?AzureMlGpuMemory $gpuMemory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $gpuMemory = $data['gpuMemory'] ?? null;

        return new self(
            gpuMemory: $gpuMemory instanceof AzureMlGpuMemory
                ? $gpuMemory
                : (is_array($gpuMemory) ? AzureMlGpuMemory::fromArray($gpuMemory) : null),
        );
    }

    public function toArray(): array
    {
        return $this->gpuMemory === null
            ? []
            : ['gpuMemory' => $this->gpuMemory->toArray()];
    }
}
