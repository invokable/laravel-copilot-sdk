<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request for reading exact file bytes via SessionFs.
 */
readonly class SessionFsReadFileBytesRequest implements Arrayable
{
    public function __construct(
        public string $path,
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path'),
            sessionId: Arr::string($data, 'sessionId'),
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'sessionId' => $this->sessionId,
        ];
    }
}
