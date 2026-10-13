<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Host presentation state used to resolve reasoning-summary visibility.
 */
readonly class ModelGetReasoningSummariesRequest implements Arrayable
{
    /**
     * @param  ?bool  $persistedShowReasoning  The host's persisted showReasoning setting, omitted when unset.
     * @param  ?bool  $summariesVisible  Whether the host is currently showing reasoning summaries.
     */
    public function __construct(
        public ?bool $persistedShowReasoning = null,
        public ?bool $summariesVisible = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            persistedShowReasoning: $data['persistedShowReasoning'] ?? null,
            summariesVisible: $data['summariesVisible'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'persistedShowReasoning' => $this->persistedShowReasoning,
            'summariesVisible' => $this->summariesVisible,
        ], fn ($value) => $value !== null);
    }
}
