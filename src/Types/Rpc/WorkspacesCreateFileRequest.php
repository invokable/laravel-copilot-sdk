<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for creating a workspace file.
 */
readonly class WorkspacesCreateFileRequest implements Arrayable
{
    /**
     * @param  string  $path  Relative path within the workspace files directory
     * @param  string  $content  File content to write as a UTF-8 string
     */
    public function __construct(
        public string $path,
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
