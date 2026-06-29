<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result for allowed-directories path check.
 */
readonly class PermissionPathsAllowedCheckResult implements Arrayable
{
    public function __construct(
        public bool $allowed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(allowed: Arr::boolean($data, 'allowed', false));
    }

    public function toArray(): array
    {
        return ['allowed' => $this->allowed];
    }
}
