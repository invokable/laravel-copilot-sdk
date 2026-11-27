<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class SkillScanInputs implements Arrayable
{
    /**
     * @param  array<int, string>  $customDirectories
     * @param  array<int, SkillScanSource>  $skillSources
     * @param  array<int, SkillScanSource>  $commandSources
     */
    public function __construct(
        public array $customDirectories,
        public array $skillSources,
        public array $commandSources,
        public ?string $traversalDirectory = null,
        public ?string $projectRoot = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customDirectories: Arr::array($data, 'customDirectories'),
            skillSources: array_map(fn (array $source) => SkillScanSource::fromArray($source), Arr::array($data, 'skillSources')),
            commandSources: array_map(fn (array $source) => SkillScanSource::fromArray($source), Arr::array($data, 'commandSources')),
            traversalDirectory: $data['traversalDirectory'] ?? null,
            projectRoot: $data['projectRoot'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'customDirectories' => $this->customDirectories,
            'skillSources' => array_map(fn (SkillScanSource $source) => $source->toArray(), $this->skillSources),
            'commandSources' => array_map(fn (SkillScanSource $source) => $source->toArray(), $this->commandSources),
            'traversalDirectory' => $this->traversalDirectory,
            'projectRoot' => $this->projectRoot,
        ], fn ($value) => $value !== null);
    }
}
