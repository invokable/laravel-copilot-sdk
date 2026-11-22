<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class ImportDiagnostic implements Arrayable
{
    public function __construct(
        public int $line,
        public string $code,
        public string $message,
        public ?string $externalId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            line: Arr::integer($data, 'line'),
            code: Arr::string($data, 'code'),
            message: Arr::string($data, 'message'),
            externalId: $data['externalId'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'line' => $this->line,
            'code' => $this->code,
            'message' => $this->message,
            'externalId' => $this->externalId,
        ], fn ($value) => $value !== null);
    }
}
