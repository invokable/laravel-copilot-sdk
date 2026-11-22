<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SessionsImportParams implements Arrayable
{
    public function __construct(
        public string $jsonl,
        public string $inputPath,
        public string $defaultWorkingDirectory,
        public ?string $workingDirectory = null,
        public ?string $name = null,
        public ?bool $dryRun = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            jsonl: Arr::string($data, 'jsonl'),
            inputPath: Arr::string($data, 'inputPath'),
            defaultWorkingDirectory: Arr::string($data, 'defaultWorkingDirectory'),
            workingDirectory: $data['workingDirectory'] ?? null,
            name: $data['name'] ?? null,
            dryRun: $data['dryRun'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'jsonl' => $this->jsonl,
            'inputPath' => $this->inputPath,
            'defaultWorkingDirectory' => $this->defaultWorkingDirectory,
            'workingDirectory' => $this->workingDirectory,
            'name' => $this->name,
            'dryRun' => $this->dryRun,
        ], fn ($value) => $value !== null);
    }
}
