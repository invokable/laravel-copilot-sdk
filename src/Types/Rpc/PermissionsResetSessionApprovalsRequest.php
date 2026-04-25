<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to reset session-scoped permission approvals.
 */
readonly class PermissionsResetSessionApprovalsRequest implements Arrayable
{
    public function __construct() {}

    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
