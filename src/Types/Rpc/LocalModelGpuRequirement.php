<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Graphics memory requirement evaluation.
 *
 * @experimental
 */
readonly class LocalModelGpuRequirement implements Arrayable
{
    public function __construct(
        public string $kind,
        public ?int $availableBytes = null,
        public ?int $requiredBytes = null,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'] ?? '',
            availableBytes: isset($data['availableBytes']) ? (int) $data['availableBytes'] : null,
            requiredBytes: isset($data['requiredBytes']) ? (int) $data['requiredBytes'] : null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            ...array_filter([
                'availableBytes' => $this->availableBytes,
                'requiredBytes' => $this->requiredBytes,
                'description' => $this->description,
            ], static fn ($value) => $value !== null),
        ];
    }
}
