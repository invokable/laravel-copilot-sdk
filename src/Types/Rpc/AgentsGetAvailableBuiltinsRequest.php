<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Options used to resolve which shipped agents are available. */
readonly class AgentsGetAvailableBuiltinsRequest implements Arrayable
{
    public function __construct(
        public ?array $featureFlags = null,
        public ?array $overrides = null,
        public ?string $context = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            featureFlags: $data['featureFlags'] ?? null,
            overrides: $data['overrides'] ?? null,
            context: $data['context'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'featureFlags' => $this->featureFlags,
            'overrides' => $this->overrides,
            'context' => $this->context,
        ], static fn ($value) => $value !== null);
    }
}
