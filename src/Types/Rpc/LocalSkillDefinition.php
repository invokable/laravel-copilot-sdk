<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\LocalSkillDefinitionSource;

/**
 * @experimental
 */
readonly class LocalSkillDefinition implements Arrayable
{
    /**
     * @param  array<int, string>  $allowedTools
     */
    public function __construct(
        public string $name,
        public string $description,
        public LocalSkillDefinitionSource|string $source,
        public string $filePath,
        public string $baseDir,
        public array $allowedTools,
        public string $content,
        public bool $userInvocable,
        public bool $disableModelInvocation,
        public bool $isCommand,
        public ?string $repositoryBoundary = null,
        public ?string $argumentHint = null,
        public ?string $pluginName = null,
        public ?string $pluginVersion = null,
        public ?string $invocationName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = Arr::string($data, 'source');

        return new self(
            name: Arr::string($data, 'name'),
            description: Arr::string($data, 'description'),
            source: LocalSkillDefinitionSource::tryFrom($source) ?? $source,
            filePath: Arr::string($data, 'filePath'),
            baseDir: Arr::string($data, 'baseDir'),
            allowedTools: Arr::array($data, 'allowedTools'),
            content: Arr::string($data, 'content'),
            userInvocable: Arr::boolean($data, 'userInvocable'),
            disableModelInvocation: Arr::boolean($data, 'disableModelInvocation'),
            isCommand: Arr::boolean($data, 'isCommand'),
            repositoryBoundary: $data['repositoryBoundary'] ?? null,
            argumentHint: $data['argumentHint'] ?? null,
            pluginName: $data['pluginName'] ?? null,
            pluginVersion: $data['pluginVersion'] ?? null,
            invocationName: $data['invocationName'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'source' => $this->source instanceof LocalSkillDefinitionSource ? $this->source->value : $this->source,
            'filePath' => $this->filePath,
            'baseDir' => $this->baseDir,
            'allowedTools' => $this->allowedTools,
            'content' => $this->content,
            'userInvocable' => $this->userInvocable,
            'disableModelInvocation' => $this->disableModelInvocation,
            'isCommand' => $this->isCommand,
            'repositoryBoundary' => $this->repositoryBoundary,
            'argumentHint' => $this->argumentHint,
            'pluginName' => $this->pluginName,
            'pluginVersion' => $this->pluginVersion,
            'invocationName' => $this->invocationName,
        ], fn ($value) => $value !== null);
    }
}
