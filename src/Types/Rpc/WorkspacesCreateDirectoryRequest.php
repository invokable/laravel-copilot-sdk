<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Directory to create within the session workspace files directory.
 *
 * @experimental
 */
readonly class WorkspacesCreateDirectoryRequest implements Arrayable
{
    /**
     * @param  string  $path  Slash-separated relative path within the workspace files directory
     * @param  ?bool  $recursive  Whether to create missing parent directories. Defaults to false.
     */
    public function __construct(
        public string $path,
        public ?bool $recursive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path'),
            recursive: $data['recursive'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'path' => $this->path,
            'recursive' => $this->recursive,
        ], fn ($v) => $v !== null);
    }
}
