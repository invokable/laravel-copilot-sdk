<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Discovered local skill used to resolve a direct removal target.
 *
 * @experimental
 */
readonly class SkillRemoveSourceEntry implements Arrayable
{
    public function __construct(
        public string $name,
        public string $source,
        public string $baseDir,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            source: $data['source'],
            baseDir: $data['baseDir'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'source' => $this->source,
            'baseDir' => $this->baseDir,
        ];
    }
}
