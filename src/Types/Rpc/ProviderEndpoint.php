<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A snapshot of the provider endpoint the session is currently configured to use.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderEndpoint implements Arrayable
{
    /**
     * @param  string  $baseUrl  Base URL to pass to the LLM client library.
     * @param  string  $type  Provider family. Matches the `type` field of a BYOK provider config.
     * @param  array<string, string>  $headers  HTTP headers the caller must include on every outbound request.
     * @param  ?string  $apiKey  A credential the caller should use with this endpoint.
     * @param  ?string  $wireApi  Wire API to be used, when required for the provider type.
     * @param  ?string  $transport  Transport to be used for provider requests.
     * @param  ProviderSessionToken|null  $sessionToken  Short-lived, rotating credential the caller must send on every request.
     */
    public function __construct(
        public string $baseUrl,
        public string $type,
        public array $headers,
        public ?string $apiKey = null,
        public ?string $wireApi = null,
        public ?string $transport = null,
        public ?ProviderSessionToken $sessionToken = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            baseUrl: Arr::string($data, 'baseUrl', ''),
            type: Arr::string($data, 'type', 'openai'),
            headers: Arr::array($data, 'headers', []),
            apiKey: $data['apiKey'] ?? null,
            wireApi: $data['wireApi'] ?? null,
            transport: $data['transport'] ?? null,
            sessionToken: isset($data['sessionToken']) ? ProviderSessionToken::fromArray($data['sessionToken']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'baseUrl' => $this->baseUrl,
            'type' => $this->type,
            'headers' => $this->headers,
            'apiKey' => $this->apiKey,
            'wireApi' => $this->wireApi,
            'transport' => $this->transport,
            'sessionToken' => $this->sessionToken?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
