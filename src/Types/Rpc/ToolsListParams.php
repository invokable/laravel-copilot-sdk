<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for listing tools.
 */
readonly class ToolsListParams implements Arrayable
{
    public function __construct(
        /** Optional model ID — when provided, the returned tool list reflects model-specific overrides */
        public ?string $model = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            model: $data['model'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'model' => $this->model,
        ], fn ($v) => $v !== null);
    }
}
