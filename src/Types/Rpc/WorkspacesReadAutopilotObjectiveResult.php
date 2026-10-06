<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Autopilot objective content, or null when the file is missing.
 *
 * @experimental
 */
readonly class WorkspacesReadAutopilotObjectiveResult implements Arrayable
{
    public function __construct(
        public ?string $content = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(content: $data['content'] ?? null);
    }

    public function toArray(): array
    {
        return ['content' => $this->content];
    }
}
