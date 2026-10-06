<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to run a provider adapter's discovery operation. */
readonly class ModelProviderDiscoverRequest implements Arrayable
{
    public function __construct(
        public string $adapterId,
        public mixed $input = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            adapterId: $data['adapterId'] ?? '',
            input: $data['input'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'adapterId' => $this->adapterId,
            'input' => $this->input,
        ], static fn ($value) => $value !== null);
    }
}
