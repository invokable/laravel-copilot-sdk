<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for enabling a skill.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionSkillsEnableParams implements Arrayable
{
    /**
     * @param  string  $name  Name of the skill to enable
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
