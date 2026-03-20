<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Information about a skill.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SkillInfo implements Arrayable
{
    /**
     * @param  string  $name  Unique identifier for the skill
     * @param  string  $description  Description of what the skill does
     * @param  string  $source  Source location type (e.g., project, personal, plugin)
     * @param  bool  $userInvocable  Whether the skill can be invoked by the user as a slash command
     * @param  bool  $enabled  Whether the skill is currently enabled
     * @param  ?string  $path  Absolute path to the skill file
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $source,
        public bool $userInvocable,
        public bool $enabled,
        public ?string $path = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'],
            source: $data['source'],
            userInvocable: $data['userInvocable'],
            enabled: $data['enabled'],
            path: $data['path'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'source' => $this->source,
            'userInvocable' => $this->userInvocable,
            'enabled' => $this->enabled,
            'path' => $this->path,
        ], fn ($v) => $v !== null);
    }
}
