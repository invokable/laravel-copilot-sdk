<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request for renaming/moving a file via SessionFs.
 */
readonly class SessionFsRenameRequest implements Arrayable
{
    /**
     * @param  string  $src  Source path using SessionFs conventions
     * @param  string  $dest  Destination path using SessionFs conventions
     * @param  string  $sessionId  Target session identifier
     */
    public function __construct(
        public string $src,
        public string $dest,
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            src: $data['src'],
            dest: $data['dest'],
            sessionId: $data['sessionId'],
        );
    }

    public function toArray(): array
    {
        return [
            'src' => $this->src,
            'dest' => $this->dest,
            'sessionId' => $this->sessionId,
        ];
    }
}
