<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Accumulated token count for one session-wide token type. */
readonly class UsageMetricsTokenDetail implements Arrayable
{
    public function __construct(
        public int $tokenCount,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(tokenCount: (int) ($data['tokenCount'] ?? 0));
    }

    public function toArray(): array
    {
        return ['tokenCount' => $this->tokenCount];
    }
}
