<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of sending zero or more user messages.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SendMessagesResult implements Arrayable
{
    /**
     * @param  string[]  $messageIds  Unique identifiers assigned to the messages, one per provided message in order
     */
    public function __construct(
        public array $messageIds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageIds: $data['messageIds'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'messageIds' => $this->messageIds,
        ];
    }
}
