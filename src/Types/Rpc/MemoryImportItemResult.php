<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class MemoryImportItemResult implements Arrayable
{
    public function __construct(
        public int $line,
        public string $status,
        public string $code,
        public string $message,
        public ?string $externalId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            line: Arr::integer($data, 'line'),
            status: Arr::string($data, 'status'),
            code: Arr::string($data, 'code'),
            message: Arr::string($data, 'message'),
            externalId: $data['externalId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'line' => $this->line,
            'externalId' => $this->externalId,
            'status' => $this->status,
            'code' => $this->code,
            'message' => $this->message,
        ], fn ($value) => $value !== null);
    }
}
