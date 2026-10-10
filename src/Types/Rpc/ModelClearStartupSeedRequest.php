<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Expected startup model/provider seed to clear before default resolution. */
readonly class ModelClearStartupSeedRequest implements Arrayable
{
    public function __construct(
        public string $expectedModel,
        public ?string $expectedProviderId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            expectedModel: $data['expectedModel'] ?? '',
            expectedProviderId: $data['expectedProviderId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'expectedModel' => $this->expectedModel,
            'expectedProviderId' => $this->expectedProviderId,
        ], fn ($value) => $value !== null);
    }
}
