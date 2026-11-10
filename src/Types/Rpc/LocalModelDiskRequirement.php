<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Local disk requirement evaluation.
 *
 * @experimental
 */
readonly class LocalModelDiskRequirement implements Arrayable
{
    public function __construct(
        public string $kind,
        public ?int $freeBytes = null,
        public ?int $requiredBytes = null,
        public ?string $directory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'] ?? '',
            freeBytes: isset($data['freeBytes']) ? (int) $data['freeBytes'] : null,
            requiredBytes: isset($data['requiredBytes']) ? (int) $data['requiredBytes'] : null,
            directory: $data['directory'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            ...array_filter([
                'freeBytes' => $this->freeBytes,
                'requiredBytes' => $this->requiredBytes,
                'directory' => $this->directory,
            ], static fn ($value) => $value !== null),
        ];
    }
}
