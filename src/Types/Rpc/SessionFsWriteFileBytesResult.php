<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of writing exact bytes through SessionFs.
 */
readonly class SessionFsWriteFileBytesResult implements Arrayable
{
    public function __construct(
        public ?SessionFsError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            error: isset($data['error']) ? SessionFsError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        return $this->error === null ? [] : ['error' => $this->error->toArray()];
    }
}
