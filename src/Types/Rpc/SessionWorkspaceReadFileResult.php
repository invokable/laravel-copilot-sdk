<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of reading a workspace file.
 */
readonly class SessionWorkspaceReadFileResult implements Arrayable
{
    /**
     * @param  string  $content  File content as a UTF-8 string
     */
    public function __construct(
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'],
        );
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
