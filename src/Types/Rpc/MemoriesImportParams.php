<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class MemoriesImportParams implements Arrayable
{
    public function __construct(
        public string $jsonl,
        public string $workingDirectory,
        public ?bool $dryRun = null,
        public ?string $onConflict = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            jsonl: Arr::string($data, 'jsonl'),
            workingDirectory: Arr::string($data, 'workingDirectory'),
            dryRun: $data['dryRun'] ?? null,
            onConflict: $data['onConflict'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'jsonl' => $this->jsonl,
            'workingDirectory' => $this->workingDirectory,
            'dryRun' => $this->dryRun,
            'onConflict' => $this->onConflict,
        ], fn ($value) => $value !== null);
    }
}
