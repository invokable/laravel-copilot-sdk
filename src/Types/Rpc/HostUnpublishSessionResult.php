<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Outcome of withdrawing a host publication. */
readonly class HostUnpublishSessionResult implements Arrayable
{
    public function __construct(
        public bool $unpublished,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            unpublished: $data['unpublished'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'unpublished' => $this->unpublished,
        ];
    }
}
