<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for loading the merged LSP configuration.
 *
 * @experimental
 */
readonly class LspInitializeRequest implements Arrayable
{
    public function __construct(
        public ?string $workingDirectory = null,
        public ?string $gitRoot = null,
        public ?bool $force = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            workingDirectory: $data['workingDirectory'] ?? null,
            gitRoot: $data['gitRoot'] ?? null,
            force: $data['force'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'workingDirectory' => $this->workingDirectory,
            'gitRoot' => $this->gitRoot,
            'force' => $this->force,
        ], fn ($value) => $value !== null);
    }
}
