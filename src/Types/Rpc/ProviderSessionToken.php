<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Short-lived, rotating credential the caller must send on every request.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderSessionToken implements Arrayable
{
    /**
     * @param  string  $header  HTTP header name the token must be sent under.
     * @param  string  $token  The short-lived token value.
     * @param  ?string  $expiresAt  When the token expires, if known (ISO 8601 datetime string).
     * @param  ?string  $model  The model the token is bound to, when applicable.
     */
    public function __construct(
        public string $header,
        public string $token,
        public ?string $expiresAt = null,
        public ?string $model = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            header: Arr::string($data, 'header', ''),
            token: Arr::string($data, 'token', ''),
            expiresAt: $data['expiresAt'] ?? null,
            model: $data['model'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'header' => $this->header,
            'token' => $this->token,
            'expiresAt' => $this->expiresAt,
            'model' => $this->model,
        ], fn ($v) => $v !== null);
    }
}
