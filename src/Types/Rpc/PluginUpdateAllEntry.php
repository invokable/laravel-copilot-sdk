<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Per-plugin result from updating all plugins.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginUpdateAllEntry implements Arrayable
{
    public function __construct(
        public string $marketplace,
        public string $name,
        public bool $success,
        public ?string $error = null,
        public ?string $newVersion = null,
        public ?string $previousVersion = null,
        public ?int $skillsInstalled = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            marketplace: Arr::string($data, 'marketplace'),
            name: Arr::string($data, 'name'),
            success: Arr::boolean($data, 'success'),
            error: $data['error'] ?? null,
            newVersion: $data['newVersion'] ?? null,
            previousVersion: $data['previousVersion'] ?? null,
            skillsInstalled: isset($data['skillsInstalled']) ? Arr::integer($data, 'skillsInstalled') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'marketplace' => $this->marketplace,
            'name' => $this->name,
            'success' => $this->success,
            'error' => $this->error,
            'newVersion' => $this->newVersion,
            'previousVersion' => $this->previousVersion,
            'skillsInstalled' => $this->skillsInstalled,
        ], fn ($value) => $value !== null);
    }
}
