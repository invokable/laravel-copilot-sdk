<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Per-marketplace refresh result.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class MarketplaceRefreshEntry implements Arrayable
{
    public function __construct(
        public string $name,
        public bool $success,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            success: Arr::boolean($data, 'success'),
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'success' => $this->success,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
