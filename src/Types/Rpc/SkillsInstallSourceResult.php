<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SkillInstallSourceKind;
use Revolution\Copilot\Enums\SkillInstallSourceScope;

/**
 * Result of direct skill source installation.
 *
 * @experimental
 */
readonly class SkillsInstallSourceResult implements Arrayable
{
    /**
     * @param  string[]  $skillDirectories
     */
    public function __construct(
        public SkillInstallSourceKind $kind,
        public string $path,
        public array $skillDirectories,
        public ?string $name = null,
        public ?SkillInstallSourceScope $scope = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: SkillInstallSourceKind::from($data['kind']),
            path: $data['path'],
            skillDirectories: $data['skillDirectories'],
            name: $data['name'] ?? null,
            scope: isset($data['scope']) ? SkillInstallSourceScope::from($data['scope']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'path' => $this->path,
            'name' => $this->name,
            'scope' => $this->scope?->value,
            'skillDirectories' => $this->skillDirectories,
        ], fn ($value) => $value !== null);
    }
}
