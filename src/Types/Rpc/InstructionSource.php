<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\InstructionSourceLocation;
use Revolution\Copilot\Enums\InstructionSourceType;

/**
 * A single instruction source discovered during session-less discovery.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class InstructionSource implements Arrayable
{
    /**
     * @param  string  $content  Raw content of the instruction file.
     * @param  string  $id  Unique identifier for this source (used for toggling).
     * @param  string  $label  Human-readable label.
     * @param  InstructionSourceLocation  $location  Where this source lives — used for UI grouping.
     * @param  string  $sourcePath  File path relative to repo or absolute for home.
     * @param  InstructionSourceType  $type  Category of instruction source — used for merge logic.
     * @param  ?string[]  $applyTo  Glob pattern(s) from frontmatter — when set, applies only to matching files.
     * @param  ?bool  $defaultDisabled  When true, this source starts disabled.
     * @param  ?string  $description  Short description for use in instruction tables.
     * @param  ?string  $projectPath  The project path this source was discovered from.
     */
    public function __construct(
        public string $content,
        public string $id,
        public string $label,
        public InstructionSourceLocation $location,
        public string $sourcePath,
        public InstructionSourceType $type,
        public ?array $applyTo = null,
        public ?bool $defaultDisabled = null,
        public ?string $description = null,
        public ?string $projectPath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: Arr::string($data, 'content'),
            id: Arr::string($data, 'id'),
            label: Arr::string($data, 'label'),
            location: InstructionSourceLocation::from($data['location']),
            sourcePath: Arr::string($data, 'sourcePath'),
            type: InstructionSourceType::from($data['type']),
            applyTo: $data['applyTo'] ?? null,
            defaultDisabled: $data['defaultDisabled'] ?? null,
            description: $data['description'] ?? null,
            projectPath: $data['projectPath'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
            'id' => $this->id,
            'label' => $this->label,
            'location' => $this->location->value,
            'sourcePath' => $this->sourcePath,
            'type' => $this->type->value,
            'applyTo' => $this->applyTo,
            'defaultDisabled' => $this->defaultDisabled,
            'description' => $this->description,
            'projectPath' => $this->projectPath,
        ], fn ($v) => $v !== null);
    }
}
