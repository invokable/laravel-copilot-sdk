<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for reading a workspace file.
 */
readonly class SessionWorkspaceReadFileParams implements Arrayable
{
    public function __construct(
        /** Relative path within the workspace files directory */
        public string $path,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'],
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
        ];
    }
}
