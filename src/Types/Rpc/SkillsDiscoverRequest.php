<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for discovering server-level skills.
 */
readonly class SkillsDiscoverRequest implements Arrayable
{
    /**
     * @param  ?array<string>  $projectPaths  Optional list of project directory paths to scan for project-scoped skills
     * @param  ?array<string>  $skillDirectories  Optional list of additional skill directory paths to include
     */
    public function __construct(
        public ?array $projectPaths = null,
        public ?array $skillDirectories = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            projectPaths: $data['projectPaths'] ?? null,
            skillDirectories: $data['skillDirectories'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'projectPaths' => $this->projectPaths,
            'skillDirectories' => $this->skillDirectories,
        ], fn ($v) => $v !== null);
    }
}
