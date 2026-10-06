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
     * @param  ?SessionFsError  $error  Structured provider error, if the read failed
     */
    public function __construct(
        public string $content,
        public ?SessionFsError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: Arr::string($data, 'content', ''),
            error: isset($data['error']) ? SessionFsError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
            'error' => $this->error?->toArray(),
        ], static fn ($value) => $value !== null);
    }
}
