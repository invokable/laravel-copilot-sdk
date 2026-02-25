<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for updating session plan.
 */
readonly class SessionPlanUpdateParams implements Arrayable
{
    public function __construct(
        /** The new content for plan.md */
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'],
        );
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
