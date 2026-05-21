<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of notifying that a permission prompt was shown.
 */
readonly class PermissionsNotifyPromptShownResult implements Arrayable
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
