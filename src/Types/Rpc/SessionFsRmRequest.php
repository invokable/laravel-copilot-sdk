<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for removing a file or directory via SessionFs.
 */
readonly class SessionFsRmRequest implements Arrayable
{
    /**
     * @param  string  $path  Path using SessionFs conventions
     * @param  string  $sessionId  Target session identifier
     * @param  ?bool  $force  Ignore errors if the path does not exist
     * @param  ?bool  $recursive  Remove directories and their contents recursively
     */
    public function __construct(
        public string $path,
        public string $sessionId,
        public ?bool $force = null,
        public ?bool $recursive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'],
            sessionId: $data['sessionId'],
            force: $data['force'] ?? null,
            recursive: $data['recursive'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'path' => $this->path,
            'sessionId' => $this->sessionId,
            'force' => $this->force,
            'recursive' => $this->recursive,
        ], fn ($v) => $v !== null);
    }
}
