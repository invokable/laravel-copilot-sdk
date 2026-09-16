<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Relative path of the workspace file or directory to inspect.
 *
 * @experimental
 */
readonly class WorkspacesStatFileRequest implements Arrayable
{
    /**
     * @param  string  $path  Slash-separated relative path within the workspace files directory
     */
    public function __construct(
        public string $path,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path'),
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
        ];
    }
}
