<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Removes a user-selected source without catalog plan semantics.
 *
 * @experimental
 */
readonly class SkillsRemoveSourceRequest implements Arrayable
{
    /**
     * @param  string[]  $skillDirectories
     */
    public function __construct(
        public string $target,
        public string $cwd,
        public array $skillDirectories,
        public ?SkillsRemoveSourceInventory $inventory = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            target: $data['target'],
            cwd: $data['cwd'],
            skillDirectories: $data['skillDirectories'],
            inventory: isset($data['inventory']) ? SkillsRemoveSourceInventory::fromArray($data['inventory']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'target' => $this->target,
            'cwd' => $this->cwd,
            'skillDirectories' => $this->skillDirectories,
            'inventory' => $this->inventory?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
