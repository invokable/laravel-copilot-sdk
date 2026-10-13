<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * On-disk workspace sizes for persisted sessions.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionSizes implements Arrayable
{
    /**
     * @param  array<string, int>  $sizes
     */
    public function __construct(
        public array $sizes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(sizes: $data['sizes'] ?? []);
    }

    public function toArray(): array
    {
        return ['sizes' => $this->sizes];
    }
}
