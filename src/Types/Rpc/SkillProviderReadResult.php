<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result containing the Markdown content for a requested skill.
 */
readonly class SkillProviderReadResult implements Arrayable
{
    public function __construct(
        public ?string $markdown,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(markdown: $data['markdown'] ?? null);
    }

    public function toArray(): array
    {
        return ['markdown' => $this->markdown];
    }
}
