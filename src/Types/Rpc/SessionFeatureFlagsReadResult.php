<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Feature evaluations and the authoritative service snapshot.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionFeatureFlagsReadResult implements Arrayable
{
    /**
     * @param  array<string, bool>  $flags
     * @param  array<int, bool|null>  $overrides
     */
    public function __construct(
        public FeatureFlagServiceSnapshot|array $snapshot,
        public array $flags,
        public array $overrides,
        public mixed $displayState = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            snapshot: $data['snapshot'] instanceof FeatureFlagServiceSnapshot
                ? $data['snapshot']
                : FeatureFlagServiceSnapshot::fromArray($data['snapshot']),
            flags: Arr::array($data, 'flags', []),
            overrides: Arr::array($data, 'overrides', []),
            displayState: $data['displayState'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'snapshot' => $this->snapshot instanceof FeatureFlagServiceSnapshot
                ? $this->snapshot->toArray()
                : $this->snapshot,
            'flags' => $this->flags,
            'overrides' => $this->overrides,
            'displayState' => $this->displayState,
        ], static fn ($value): bool => $value !== null);
    }
}
