<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for creating a directory via SessionFs.
 */
readonly class SessionFsMkdirParams implements Arrayable
{
    /**
     * @param  string  $path  Path using SessionFs conventions
     * @param  string  $sessionId  Target session identifier
     * @param  ?int  $mode  Optional POSIX-style mode for newly created directories
     * @param  ?bool  $recursive  Create parent directories as needed
     */
    public function __construct(
        public string $path,
        public string $sessionId,
        public ?int $mode = null,
        public ?bool $recursive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'],
            sessionId: $data['sessionId'],
            mode: isset($data['mode']) ? (int) $data['mode'] : null,
            recursive: $data['recursive'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'path' => $this->path,
            'sessionId' => $this->sessionId,
            'mode' => $this->mode,
            'recursive' => $this->recursive,
        ], fn ($v) => $v !== null);
    }
}
