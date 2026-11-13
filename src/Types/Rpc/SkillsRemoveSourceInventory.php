<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Caller-owned inventory and storage roots for direct file removal.
 *
 * @experimental
 */
readonly class SkillsRemoveSourceInventory implements Arrayable
{
    /**
     * @param  SkillRemoveSourceEntry[]  $skills
     * @param  string[]  $personalSkillsDirs
     */
    public function __construct(
        public array $skills,
        public array $personalSkillsDirs,
        public string $projectSkillsDir,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            skills: array_map(
                fn (array $skill) => SkillRemoveSourceEntry::fromArray($skill),
                $data['skills'],
            ),
            personalSkillsDirs: $data['personalSkillsDirs'],
            projectSkillsDir: $data['projectSkillsDir'],
        );
    }

    public function toArray(): array
    {
        return [
            'skills' => array_map(fn (SkillRemoveSourceEntry $skill) => $skill->toArray(), $this->skills),
            'personalSkillsDirs' => $this->personalSkillsDirs,
            'projectSkillsDir' => $this->projectSkillsDir,
        ];
    }
}
