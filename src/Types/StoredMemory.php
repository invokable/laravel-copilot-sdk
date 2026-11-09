<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A memory the agent asked the application to store.
 */
readonly class StoredMemory implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $subject,
        public string $fact,
        public string $citations,
        public string $reason,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            subject: Arr::string($data, 'subject'),
            fact: Arr::string($data, 'fact'),
            citations: Arr::string($data, 'citations'),
            reason: Arr::string($data, 'reason'),
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'subject' => $this->subject,
            'fact' => $this->fact,
            'citations' => $this->citations,
            'reason' => $this->reason,
        ];
    }
}
