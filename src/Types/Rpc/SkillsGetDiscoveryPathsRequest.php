<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional project paths to enumerate skill discovery paths.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SkillsGetDiscoveryPathsRequest implements Arrayable
{
    /**
     * @param  ?bool  $excludeHostSkills  When true, omit the host's personal and custom skill directories.
     * @param  ?array<string>  $projectPaths  Optional list of project directory paths.
     */
    public function __construct(
        public ?bool $excludeHostSkills = null,
        public ?array $projectPaths = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            excludeHostSkills: $data['excludeHostSkills'] ?? null,
            projectPaths: $data['projectPaths'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'excludeHostSkills' => $this->excludeHostSkills,
            'projectPaths' => $this->projectPaths,
        ], fn ($v) => $v !== null);
    }
}
