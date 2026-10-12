<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Plugin source and optional working directory for relative-path resolution.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginsInstallRequest implements Arrayable
{
    public function __construct(
        public string $source,
        public ?string $workingDirectory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            source: Arr::string($data, 'source'),
            workingDirectory: $data['workingDirectory'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'source' => $this->source,
            'workingDirectory' => $this->workingDirectory,
        ], fn ($value) => $value !== null);
    }
}
