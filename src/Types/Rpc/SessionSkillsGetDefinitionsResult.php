<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class SessionSkillsGetDefinitionsResult implements Arrayable
{
    /**
     * @param  array<int, LocalSkillDefinition>  $skills
     * @param  array<int, string>  $warnings
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public array $skills,
        public array $warnings,
        public array $errors,
        public string $displayCwd,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            skills: array_map(fn (array $skill) => LocalSkillDefinition::fromArray($skill), Arr::array($data, 'skills')),
            warnings: Arr::array($data, 'warnings'),
            errors: Arr::array($data, 'errors'),
            displayCwd: Arr::string($data, 'displayCwd'),
        );
    }

    public function toArray(): array
    {
        return [
            'skills' => array_map(fn (LocalSkillDefinition $skill) => $skill->toArray(), $this->skills),
            'warnings' => $this->warnings,
            'errors' => $this->errors,
            'displayCwd' => $this->displayCwd,
        ];
    }
}
