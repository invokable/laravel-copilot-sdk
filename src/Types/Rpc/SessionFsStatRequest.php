<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request for getting file stats via SessionFs.
 */
readonly class SessionFsStatRequest implements Arrayable
{
    /**
     * @param  string  $path  Path using SessionFs conventions
     * @param  string  $sessionId  Target session identifier
     */
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
