<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Pending user-facing quota warning. */
readonly class QuotaWarningProjection implements Arrayable
{
    public function __construct(
        public string $warningType,
        public string $message,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            warningType: $data['warningType'] ?? '',
            message: $data['message'] ?? '',
            url: $data['url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'warningType' => $this->warningType,
            'message' => $this->message,
            'url' => $this->url,
        ], fn ($value) => $value !== null);
    }
}
