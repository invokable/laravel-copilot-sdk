<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request for writing a file via SessionFs.
 */
readonly class SessionFsWriteFileRequest implements Arrayable
{
    /**
     * @param  string  $content  Content to write
     * @param  string  $path  Path using SessionFs conventions
     * @param  string  $sessionId  Target session identifier
     * @param  ?int  $mode  Optional POSIX-style mode for newly created files
     */
    public function __construct(
        public string $content,
        public string $path,
        public string $sessionId,
        public ?int $mode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: Arr::string($data, 'content'),
            path: Arr::string($data, 'path'),
            sessionId: Arr::string($data, 'sessionId'),
            mode: isset($data['mode']) ? (int) $data['mode'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
            'path' => $this->path,
            'sessionId' => $this->sessionId,
            'mode' => $this->mode,
        ], fn ($v) => $v !== null);
    }
}
