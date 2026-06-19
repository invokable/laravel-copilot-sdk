<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\InstructionDiscoveryPathKind;
use Revolution\Copilot\Enums\InstructionSourceLocation;

/**
 * Schema for the InstructionDiscoveryPath type.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class InstructionDiscoveryPath implements Arrayable
{
    /**
     * @param  InstructionDiscoveryPathKind  $kind  Whether the target is a single file or a directory
     * @param  InstructionSourceLocation  $location  Which tier this target belongs to
     * @param  string  $path  Absolute path of the file or directory (may not exist on disk yet)
     * @param  bool  $preferredForCreation  Whether this is the canonical target to create new instructions in its tier
     * @param  ?string  $projectPath  The input project path this target was derived from (only for repository targets)
     */
    public function __construct(
        public InstructionDiscoveryPathKind $kind,
        public InstructionSourceLocation $location,
        public string $path,
        public bool $preferredForCreation,
        public ?string $projectPath = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: InstructionDiscoveryPathKind::from($data['kind'] ?? 'file'),
            location: InstructionSourceLocation::from($data['location'] ?? 'user'),
            path: $data['path'] ?? '',
            preferredForCreation: (bool) ($data['preferredForCreation'] ?? false),
            projectPath: $data['projectPath'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'location' => $this->location->value,
            'path' => $this->path,
            'preferredForCreation' => $this->preferredForCreation,
            'projectPath' => $this->projectPath,
        ], fn ($v) => $v !== null);
    }
}
