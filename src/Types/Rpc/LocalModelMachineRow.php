<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * One local model and its current files, progress, and requirements.
 *
 * @experimental
 */
readonly class LocalModelMachineRow implements Arrayable
{
    public function __construct(
        public LocalModelDescriptor $descriptor,
        public LocalModelStatus $status,
        public int $stagedBytes,
        public int $leftoverBytes,
        public LocalModelDiskRequirement $disk,
        public LocalModelGpuRequirement $gpu,
        public bool $diskMet,
        public ?int $diskRequiredBytes = null,
        public ?int $gpuRequiredBytes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $descriptor = $data['descriptor'] ?? [];
        $status = $data['status'] ?? [];
        $disk = $data['disk'] ?? [];
        $gpu = $data['gpu'] ?? [];

        return new self(
            descriptor: $descriptor instanceof LocalModelDescriptor
                ? $descriptor
                : LocalModelDescriptor::fromArray(is_array($descriptor) ? $descriptor : []),
            status: $status instanceof LocalModelStatus
                ? $status
                : LocalModelStatus::fromArray(is_array($status) ? $status : []),
            stagedBytes: (int) ($data['stagedBytes'] ?? 0),
            leftoverBytes: (int) ($data['leftoverBytes'] ?? 0),
            disk: $disk instanceof LocalModelDiskRequirement
                ? $disk
                : LocalModelDiskRequirement::fromArray(is_array($disk) ? $disk : []),
            gpu: $gpu instanceof LocalModelGpuRequirement
                ? $gpu
                : LocalModelGpuRequirement::fromArray(is_array($gpu) ? $gpu : []),
            diskRequiredBytes: isset($data['diskRequiredBytes']) ? (int) $data['diskRequiredBytes'] : null,
            diskMet: $data['diskMet'] ?? false,
            gpuRequiredBytes: isset($data['gpuRequiredBytes']) ? (int) $data['gpuRequiredBytes'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'descriptor' => $this->descriptor->toArray(),
            'status' => $this->status->toArray(),
            'stagedBytes' => $this->stagedBytes,
            'leftoverBytes' => $this->leftoverBytes,
            'disk' => $this->disk->toArray(),
            'gpu' => $this->gpu->toArray(),
            ...array_filter([
                'diskRequiredBytes' => $this->diskRequiredBytes,
            ], static fn ($value) => $value !== null),
            'diskMet' => $this->diskMet,
            ...array_filter([
                'gpuRequiredBytes' => $this->gpuRequiredBytes,
            ], static fn ($value) => $value !== null),
        ];
    }
}
