<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SkillRemoveSourceKind;

/**
 * Result of direct skill source removal.
 *
 * @experimental
 */
readonly class SkillsRemoveSourceResult implements Arrayable
{
    /**
     * @param  string[]  $skillDirectories
     */
    public function __construct(
        public SkillRemoveSourceKind $kind,
        public string $path,
        public array $skillDirectories,
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: SkillRemoveSourceKind::from($data['kind']),
            path: $data['path'],
            skillDirectories: $data['skillDirectories'],
            name: $data['name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'path' => $this->path,
            'name' => $this->name,
            'skillDirectories' => $this->skillDirectories,
        ], fn ($value) => $value !== null);
    }
}
