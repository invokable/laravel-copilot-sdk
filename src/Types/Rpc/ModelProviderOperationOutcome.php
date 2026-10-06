<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderOperationOutcome implements Arrayable
{
    public function __construct(
        public string $code,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? '',
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'message' => $this->message,
        ], static fn ($value) => $value !== null);
    }
}
