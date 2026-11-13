<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Installs a user-selected skill source without catalog plan semantics.
 *
 * @experimental
 */
readonly class SkillsInstallSourceRequest implements Arrayable
{
    /**
     * @param  string[]  $skillDirectories
     */
    public function __construct(
        public string $source,
        public string $cwd,
        public string $personalSkillsDir,
        public string $projectSkillsDir,
        public bool $project,
        public array $skillDirectories,
        public string $userAgent,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            source: $data['source'],
            cwd: $data['cwd'],
            personalSkillsDir: $data['personalSkillsDir'],
            projectSkillsDir: $data['projectSkillsDir'],
            project: $data['project'],
            skillDirectories: $data['skillDirectories'],
            userAgent: $data['userAgent'],
        );
    }

    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'cwd' => $this->cwd,
            'personalSkillsDir' => $this->personalSkillsDir,
            'projectSkillsDir' => $this->projectSkillsDir,
            'project' => $this->project,
            'skillDirectories' => $this->skillDirectories,
            'userAgent' => $this->userAgent,
        ];
    }
}
