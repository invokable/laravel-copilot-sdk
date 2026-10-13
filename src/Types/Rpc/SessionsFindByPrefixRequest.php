<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Look up a unique session ID by UUID prefix.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsFindByPrefixRequest implements Arrayable
{
    public function __construct(
        public string $prefix,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(prefix: $data['prefix']);
    }

    public function toArray(): array
    {
        return ['prefix' => $this->prefix];
    }
}
