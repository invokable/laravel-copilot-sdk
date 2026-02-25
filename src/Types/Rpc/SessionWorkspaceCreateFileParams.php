<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for creating a workspace file.
 */
readonly class SessionWorkspaceCreateFileParams implements Arrayable
{
    public function __construct(
        /** Relative path within the workspace files directory */
        public string $path,
        /** File content to write as a UTF-8 string */
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'],
            content: $data['content'],
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'content' => $this->content,
        ];
    }
}
