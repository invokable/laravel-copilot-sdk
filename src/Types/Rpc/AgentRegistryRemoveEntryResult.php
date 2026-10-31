<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The outcome of removing a registry entry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryRemoveEntryResult implements Arrayable
{
    public function __construct(
        public ?AgentRegistryError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            error: isset($data['error']) ? AgentRegistryError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'error' => $this->error?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
