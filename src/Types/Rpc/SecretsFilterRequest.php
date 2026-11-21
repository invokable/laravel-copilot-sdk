<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SecretFilterFormat;

/** @experimental */
readonly class SecretsFilterRequest implements Arrayable
{
    public function __construct(
        public string $text,
        public SecretFilterFormat|string|null $format = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $format = $data['format'] ?? null;

        return new self(
            text: Arr::string($data, 'text'),
            format: is_string($format) ? (SecretFilterFormat::tryFrom($format) ?? $format) : $format,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'format' => $this->format instanceof SecretFilterFormat ? $this->format->value : $this->format,
        ], fn ($value) => $value !== null);
    }
}
