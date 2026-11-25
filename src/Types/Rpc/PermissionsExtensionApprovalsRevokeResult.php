<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class PermissionsExtensionApprovalsRevokeResult implements Arrayable
{
    public function __construct(
        public bool $revoked,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            revoked: Arr::boolean($data, 'revoked'),
        );
    }

    public function toArray(): array
    {
        return ['revoked' => $this->revoked];
    }
}
