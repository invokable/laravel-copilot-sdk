<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Plugin names or specs to disable.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginsDisableRequest implements Arrayable
{
    /**
     * @param  array<string>  $names
     */
    public function __construct(
        public array $names,
        public ?string $workingDirectory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            names: $data['names'] ?? [],
            workingDirectory: $data['workingDirectory'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'names' => $this->names,
            'workingDirectory' => $this->workingDirectory,
        ], fn ($value) => $value !== null);
    }
}
