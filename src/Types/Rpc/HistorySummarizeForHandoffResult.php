<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of summarizing session history for handoff.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class HistorySummarizeForHandoffResult implements Arrayable
{
    /**
     * @param  string  $summary  Summary of the session history
     */
    public function __construct(
        public string $summary,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            summary: Arr::string($data, 'summary'),
        );
    }

    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
        ];
    }
}
