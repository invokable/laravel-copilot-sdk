<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Sends peer text, never human approval, to another opted-in session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementMessageRequest implements Arrayable
{
    public function __construct(
        public string $targetSessionId,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            targetSessionId: $data['targetSessionId'],
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'targetSessionId' => $this->targetSessionId,
            'message' => $this->message,
        ];
    }
}
