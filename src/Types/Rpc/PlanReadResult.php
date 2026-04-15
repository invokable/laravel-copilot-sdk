<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of reading session plan.
 */
readonly class PlanReadResult implements Arrayable
{
    /**
     * @param  bool  $exists  Whether the plan file exists in the workspace
     * @param  ?string  $content  The content of the plan file, or null if it does not exist
     * @param  ?string  $path  Absolute file path of the plan file, or null if workspace is not enabled
     */
    public function __construct(
        public bool $exists,
        public ?string $content = null,
        public ?string $path = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            exists: $data['exists'],
            content: $data['content'] ?? null,
            path: $data['path'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'exists' => $this->exists,
            'content' => $this->content,
            'path' => $this->path,
        ];
    }
}
