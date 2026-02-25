<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of reading session plan.
 */
readonly class SessionPlanReadResult implements Arrayable
{
    public function __construct(
        /** Whether plan.md exists in the workspace */
        public bool $exists,
        /** The content of plan.md, or null if it does not exist */
        public ?string $content = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            exists: $data['exists'],
            content: $data['content'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'exists' => $this->exists,
            'content' => $this->content,
        ];
    }
}
