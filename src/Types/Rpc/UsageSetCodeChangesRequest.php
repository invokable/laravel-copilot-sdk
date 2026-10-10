<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Absolute code-change totals reported by a relay host. */
readonly class UsageSetCodeChangesRequest implements Arrayable
{
    public function __construct(
        public int $linesAdded,
        public int $linesRemoved,
        public ?int $filesCount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            linesAdded: (int) ($data['linesAdded'] ?? 0),
            linesRemoved: (int) ($data['linesRemoved'] ?? 0),
            filesCount: isset($data['filesCount']) ? (int) $data['filesCount'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'linesAdded' => $this->linesAdded,
            'linesRemoved' => $this->linesRemoved,
            'filesCount' => $this->filesCount,
        ], fn ($value) => $value !== null);
    }
}
