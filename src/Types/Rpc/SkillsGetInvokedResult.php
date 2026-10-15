<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Skills that have been invoked during a session.
 */
readonly class SkillsGetInvokedResult implements Arrayable
{
    /**
     * @param  SkillsInvokedSkill[]  $skills
     */
    public function __construct(
        public array $skills = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            skills: array_map(
                SkillsInvokedSkill::fromArray(...),
                Arr::array($data, 'skills', []),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'skills' => array_map(
                fn (SkillsInvokedSkill $skill) => $skill->toArray(),
                $this->skills,
            ),
        ];
    }
}
