<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\InstructionsSourcesLocation;
use Revolution\Copilot\Enums\InstructionsSourcesType;

/**
 * A single instruction source for a session.
 */
readonly class InstructionsSources implements Arrayable
{
    /**
     * @param  string  $id  Unique identifier for this source (used for toggling)
     * @param  string  $label  Human-readable label
     * @param  string  $content  Raw content of the instruction file
     * @param  string  $sourcePath  File path relative to repo or absolute for home
     * @param  InstructionsSourcesType  $type  Category of instruction source — used for merge logic
     * @param  InstructionsSourcesLocation  $location  Where this source lives — used for UI grouping
     * @param  ?string  $applyTo  Glob pattern from frontmatter — when set, this instruction applies only to matching files
     * @param  ?string  $description  Short description (body after frontmatter) for use in instruction tables
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $content,
        public string $sourcePath,
        public InstructionsSourcesType $type,
        public InstructionsSourcesLocation $location,
        public ?string $applyTo = null,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            label: $data['label'],
            content: $data['content'],
            sourcePath: $data['sourcePath'],
            type: InstructionsSourcesType::from($data['type']),
            location: InstructionsSourcesLocation::from($data['location']),
            applyTo: $data['applyTo'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'label' => $this->label,
            'content' => $this->content,
            'sourcePath' => $this->sourcePath,
            'type' => $this->type->value,
            'location' => $this->location->value,
            'applyTo' => $this->applyTo,
            'description' => $this->description,
        ], fn ($v) => $v !== null);
    }
}
