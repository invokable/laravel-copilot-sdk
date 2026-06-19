<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional project paths to include when enumerating instruction discovery targets.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class InstructionsGetDiscoveryPathsRequest implements Arrayable
{
    /**
     * @param  ?bool  $excludeHostInstructions  When true, omit the host's user-level instruction targets.
     * @param  ?array<string>  $projectPaths  Optional list of project directory paths.
     */
    public function __construct(
        public ?bool $excludeHostInstructions = null,
        public ?array $projectPaths = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            excludeHostInstructions: $data['excludeHostInstructions'] ?? null,
            projectPaths: $data['projectPaths'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'excludeHostInstructions' => $this->excludeHostInstructions,
            'projectPaths' => $this->projectPaths,
        ], fn ($v) => $v !== null);
    }
}
