<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for disabling a skill.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionSkillsDisableParams implements Arrayable
{
    /**
     * @param  string  $name  Name of the skill to disable
     */
    public function __construct(
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
