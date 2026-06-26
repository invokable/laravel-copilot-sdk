<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A named BYOK provider connection (transport + credentials), referenced by
 * ProviderModelConfig entries via name.
 *
 * Unlike the singular ProviderConfig — which makes the entire session BYOK and bypasses
 * Copilot API authentication — named providers are additive: they coexist with Copilot
 * API auth so models from CAPI and one or more BYOK providers can be mixed within a single
 * session and across sub-agents. Combining providers/models with provider is rejected.
 *
 * @experimental This type is part of an experimental multi-provider BYOK surface
 * and may change or be removed in future SDK or CLI releases.
 */
readonly class NamedProviderConfig implements Arrayable
{
    /**
     * @param  string  $name  Stable identifier referenced by ProviderModelConfig. Must not contain '/'.
     * @param  string  $baseUrl  API endpoint URL.
     * @param  ?string  $type  Provider type. Defaults to "openai" for generic OpenAI-compatible APIs.
     * @param  ?string  $wireApi  Wire API format (openai/azure only). Defaults to "completions".
     * @param  ?string  $transport  Provider transport. Defaults to "http".
     * @param  ?string  $apiKey  API key. Optional for local providers like Ollama.
     * @param  ?string  $bearerToken  Bearer token for authentication. Takes precedence over apiKey when both are set.
     * @param  ?bool  $hasBearerTokenProvider  When true, the SDK client supplies bearer tokens on demand via a callback.
     * @param  ?array  $azure  Azure-specific options.
     * @param  ?array<string, string>  $headers  Custom HTTP headers to include in all outbound requests to the provider.
     */
    public function __construct(
        public string $name,
        public string $baseUrl,
        public ?string $type = null,
        public ?string $wireApi = null,
        public ?string $transport = null,
        public ?string $apiKey = null,
        public ?string $bearerToken = null,
        public ?bool $hasBearerTokenProvider = null,
        public ?array $azure = null,
        public ?array $headers = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            baseUrl: $data['baseUrl'] ?? '',
            type: $data['type'] ?? null,
            wireApi: $data['wireApi'] ?? null,
            transport: $data['transport'] ?? null,
            apiKey: $data['apiKey'] ?? null,
            bearerToken: $data['bearerToken'] ?? null,
            hasBearerTokenProvider: $data['hasBearerTokenProvider'] ?? null,
            azure: $data['azure'] ?? null,
            headers: $data['headers'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'baseUrl' => $this->baseUrl,
            'type' => $this->type,
            'wireApi' => $this->wireApi,
            'transport' => $this->transport,
            'apiKey' => $this->apiKey,
            'bearerToken' => $this->bearerToken,
            'hasBearerTokenProvider' => $this->hasBearerTokenProvider,
            'azure' => $this->azure,
            'headers' => $this->headers,
        ], fn ($v) => $v !== null);
    }
}
