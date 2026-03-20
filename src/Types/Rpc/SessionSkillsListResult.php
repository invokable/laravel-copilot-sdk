<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of listing skills.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionSkillsListResult implements Arrayable
{
    /**
     * @param  array<SkillInfo>  $skills  Available skills
     */
    public function __construct(
        public array $skills,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            skills: array_map(
                fn (array $skill) => SkillInfo::fromArray($skill),
                $data['skills'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'skills' => array_map(fn (SkillInfo $skill) => $skill->toArray(), $this->skills),
        ];
    }
}
