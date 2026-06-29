<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Payload for permission prompt shown notification.
 */
readonly class PermissionPromptShownNotification implements Arrayable
{
    public function __construct(
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: Arr::string($data, 'message'),
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
