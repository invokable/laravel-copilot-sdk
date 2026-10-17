<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\DebugCollectLogsSource;

/**
 * A file or directory successfully added to a debug log bundle.
 *
 * @experimental
 */
readonly class DebugCollectLogsCollectedEntry implements Arrayable
{
    public function __construct(
        public string $bundlePath,
        public DebugCollectLogsSource|string $source,
        public int $sizeBytes,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = $data['source'];

        return new self(
            bundlePath: $data['bundlePath'],
            source: is_string($source) ? DebugCollectLogsSource::tryFrom($source) ?? $source : $source,
            sizeBytes: $data['sizeBytes'],
        );
    }

    public function toArray(): array
    {
        return [
            'bundlePath' => $this->bundlePath,
            'source' => $this->source instanceof DebugCollectLogsSource ? $this->source->value : $this->source,
            'sizeBytes' => $this->sizeBytes,
        ];
    }
}
