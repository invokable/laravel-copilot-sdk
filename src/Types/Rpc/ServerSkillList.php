<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * List of server-level skills discovered by the CLI.
 */
readonly class ServerSkillList implements Arrayable
{
    /**
     * @param  array<ServerSkill>  $skills  All discovered skills across all sources
     */
    public function __construct(
        public array $skills,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            skills: array_map(
                fn (array $skill) => ServerSkill::fromArray($skill),
                $data['skills'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'skills' => array_map(
                fn (ServerSkill $skill) => $skill->toArray(),
                $this->skills,
            ),
        ];
    }
}
