<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FetchFileErrorKind;
use Revolution\Copilot\Enums\FetchFileOperation;

/** @experimental */
readonly class FetchFileErrorData implements Arrayable
{
    public function __construct(
        public FetchFileErrorKind $kind,
        public FetchFileOperation $operation,
        public ?int $osErrorCode = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: FetchFileErrorKind::from(Arr::string($data, 'kind', '')),
            operation: FetchFileOperation::from(Arr::string($data, 'operation', '')),
            osErrorCode: isset($data['osErrorCode']) ? Arr::integer($data, 'osErrorCode') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'operation' => $this->operation->value,
            'osErrorCode' => $this->osErrorCode,
        ], static fn ($value): bool => $value !== null);
    }
}
