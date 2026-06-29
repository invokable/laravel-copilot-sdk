<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of reading a file via SessionFs.
 */
readonly class SessionFsReadFileResult implements Arrayable
{
    /**
     * @param  string  $content  File content as UTF-8 string
     */
    public function __construct(
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: Arr::string($data, 'content', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
