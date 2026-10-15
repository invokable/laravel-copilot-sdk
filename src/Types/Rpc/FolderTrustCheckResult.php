<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether a path is trusted.
 */
readonly class FolderTrustCheckResult implements Arrayable
{
    public function __construct(
        public bool $trusted,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(trusted: Arr::boolean($data, 'trusted', false));
    }

    public function toArray(): array
    {
        return ['trusted' => $this->trusted];
    }
}
