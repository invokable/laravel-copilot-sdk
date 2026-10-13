<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether this host can run one sandbox policy feature.
 */
readonly class SandboxHostCapability implements Arrayable
{
    public function __construct(
        public string $name,
        public bool $supported,
        public ?string $reason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            supported: Arr::boolean($data, 'supported', false),
            reason: isset($data['reason']) ? Arr::string($data, 'reason') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'supported' => $this->supported,
            'reason' => $this->reason,
        ], fn ($value): bool => $value !== null);
    }
}
