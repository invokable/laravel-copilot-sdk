<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of updating a single plugin.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginUpdateResult implements Arrayable
{
    public function __construct(
        public int $skillsInstalled,
        public ?string $newVersion = null,
        public ?string $previousVersion = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            skillsInstalled: Arr::integer($data, 'skillsInstalled'),
            newVersion: $data['newVersion'] ?? null,
            previousVersion: $data['previousVersion'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'skillsInstalled' => $this->skillsInstalled,
            'newVersion' => $this->newVersion,
            'previousVersion' => $this->previousVersion,
        ], fn ($value) => $value !== null);
    }
}
