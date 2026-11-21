<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\AgentFileLocation;
use Revolution\Copilot\Enums\AgentToolCategoryId;

/** Parameters for creating an agent definition file. */
readonly class AgentsCreateFileRequest implements Arrayable
{
    /**
     * @param  AgentToolCategoryId[]|null  $enabledCategories
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $instructions,
        public AgentFileLocation $location,
        public string $projectPath,
        public string $userConfigDir,
        public ?array $enabledCategories = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            description: Arr::string($data, 'description'),
            instructions: Arr::string($data, 'instructions'),
            enabledCategories: isset($data['enabledCategories'])
                ? array_map(
                    static fn (string $category): AgentToolCategoryId => AgentToolCategoryId::from($category),
                    Arr::array($data, 'enabledCategories'),
                )
                : null,
            location: AgentFileLocation::from(Arr::string($data, 'location')),
            projectPath: Arr::string($data, 'projectPath'),
            userConfigDir: Arr::string($data, 'userConfigDir'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'enabledCategories' => $this->enabledCategories === null
                ? null
                : array_map(static fn (AgentToolCategoryId $category): string => $category->value, $this->enabledCategories),
            'location' => $this->location->value,
            'projectPath' => $this->projectPath,
            'userConfigDir' => $this->userConfigDir,
        ], static fn ($value): bool => $value !== null);
    }
}
