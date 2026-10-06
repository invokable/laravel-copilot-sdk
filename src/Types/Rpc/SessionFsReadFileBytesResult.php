<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of reading exact file bytes; content is base64 encoded on the wire.
 */
readonly class SessionFsReadFileBytesResult implements Arrayable
{
    public function __construct(
        public string $content,
        public ?SessionFsError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? '',
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
