<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of listing workspace files.
 */
readonly class SessionWorkspaceListFilesResult implements Arrayable
{
    /**
     * @param  array<string>  $files  Relative file paths in the workspace files directory
     */
    public function __construct(
        public array $files,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            files: $data['files'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'files' => $this->files,
        ];
    }
}
