<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of session permission rules modification.
 */
readonly class PermissionsModifyRulesResult implements Arrayable
{
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
