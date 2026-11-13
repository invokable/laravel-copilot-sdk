<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FetchTransportErrorKind;

/** @experimental */
readonly class FetchTransportErrorData implements Arrayable
{
    public function __construct(
        public FetchTransportErrorKind $kind,
        public bool $permanentRequestFailure,
        public bool $networkError,
        public ?string $code = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: FetchTransportErrorKind::from(Arr::string($data, 'kind', '')),
            permanentRequestFailure: Arr::boolean($data, 'permanentRequestFailure', false),
            networkError: Arr::boolean($data, 'networkError', false),
            code: isset($data['code']) ? Arr::string($data, 'code') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'code' => $this->code,
            'permanentRequestFailure' => $this->permanentRequestFailure,
            'networkError' => $this->networkError,
        ], static fn ($value): bool => $value !== null);
    }
}
