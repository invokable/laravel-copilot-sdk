<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of session compaction.
 */
readonly class SessionCompactionCompactResult implements Arrayable
{
    public function __construct(
        /** Whether compaction completed successfully */
        public bool $success,
        /** Number of tokens freed by compaction */
        public int $tokensRemoved,
        /** Number of messages removed during compaction */
        public int $messagesRemoved,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            tokensRemoved: $data['tokensRemoved'],
            messagesRemoved: $data['messagesRemoved'],
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'tokensRemoved' => $this->tokensRemoved,
            'messagesRemoved' => $this->messagesRemoved,
        ];
    }
}
