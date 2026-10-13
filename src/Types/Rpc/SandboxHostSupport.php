<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether the host running this runtime can run the command sandbox.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxHostSupport implements Arrayable
{
    /**
     * @param  SandboxHostCapability[]  $capabilities
     */
    public function __construct(
        public bool $supported,
        public ?string $reason = null,
        public array $capabilities = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            supported: Arr::boolean($data, 'supported', false),
            reason: isset($data['reason']) ? Arr::string($data, 'reason') : null,
            capabilities: array_map(
                fn (array $capability): SandboxHostCapability => SandboxHostCapability::fromArray($capability),
                $data['capabilities'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'supported' => $this->supported,
            'reason' => $this->reason,
            'capabilities' => array_map(
                fn (SandboxHostCapability $capability): array => $capability->toArray(),
                $this->capabilities,
            ),
        ], fn ($value): bool => $value !== null);
    }
}
