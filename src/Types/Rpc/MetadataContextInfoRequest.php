<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for computing context token breakdown.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataContextInfoRequest implements Arrayable
{
    public function __construct(
        public int $promptTokenLimit,
        public int $outputTokenLimit,
        public ?string $selectedModel = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            promptTokenLimit: $data['promptTokenLimit'] ?? 0,
            outputTokenLimit: $data['outputTokenLimit'] ?? 0,
            selectedModel: $data['selectedModel'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'promptTokenLimit' => $this->promptTokenLimit,
            'outputTokenLimit' => $this->outputTokenLimit,
            'selectedModel' => $this->selectedModel,
        ], fn ($value): bool => $value !== null);
    }
}
