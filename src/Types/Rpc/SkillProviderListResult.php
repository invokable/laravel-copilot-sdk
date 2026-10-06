<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result containing the catalog supplied by a session's skill provider.
 */
readonly class SkillProviderListResult implements Arrayable
{
    /**
     * @param  array<array<string, mixed>>  $skills
     */
    public function __construct(
        public array $skills,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(skills: $data['skills'] ?? []);
    }

    public function toArray(): array
    {
        return ['skills' => $this->skills];
    }
}
