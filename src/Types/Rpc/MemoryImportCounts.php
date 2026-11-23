<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class MemoryImportCounts implements Arrayable
{
    public function __construct(
        public int $total,
        public int $imported,
        public int $skipped,
        public int $failed,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            total: Arr::integer($data, 'total'),
            imported: Arr::integer($data, 'imported'),
            skipped: Arr::integer($data, 'skipped'),
            failed: Arr::integer($data, 'failed'),
        );
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
    }
}
