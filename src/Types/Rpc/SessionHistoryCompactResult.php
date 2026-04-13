<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of session history compaction.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionHistoryCompactResult implements Arrayable
{
    /**
     * @param  bool  $success  Whether compaction completed successfully
     * @param  int  $tokensRemoved  Number of tokens freed by compaction
     * @param  int  $messagesRemoved  Number of messages removed during compaction
     * @param  ?ContextWindow  $contextWindow  Post-compaction context window usage breakdown
     */
    public function __construct(
        public bool $success,
        public int $tokensRemoved,
        public int $messagesRemoved,
        public ?ContextWindow $contextWindow = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'],
            tokensRemoved: $data['tokensRemoved'],
            messagesRemoved: $data['messagesRemoved'],
            contextWindow: isset($data['contextWindow']) ? ContextWindow::fromArray($data['contextWindow']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'tokensRemoved' => $this->tokensRemoved,
            'messagesRemoved' => $this->messagesRemoved,
            'contextWindow' => $this->contextWindow?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
