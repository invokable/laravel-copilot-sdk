<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * An entry that could not be added to a debug log bundle.
 *
 * @experimental
 */
readonly class DebugCollectLogsSkippedEntry implements Arrayable
{
    public function __construct(
        public string $bundlePath,
        public ?string $path,
        public string $reason,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bundlePath: $data['bundlePath'],
            path: $data['path'] ?? null,
            reason: $data['reason'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'bundlePath' => $this->bundlePath,
            'path' => $this->path,
            'reason' => $this->reason,
        ], fn ($value) => $value !== null);
    }
}
