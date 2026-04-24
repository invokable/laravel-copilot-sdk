<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of resetting session-scoped permission approvals.
 */
readonly class PermissionsResetSessionApprovalsResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether the operation succeeded.
     */
    public function __construct(
        public bool $success,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
        ];
    }
}
