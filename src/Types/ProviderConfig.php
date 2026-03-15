<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Configuration for a custom API provider.
 */
readonly class ProviderConfig implements Arrayable
{
    /**
     * @param  string  $baseUrl  API endpoint URL
     * @param  ?string  $type  Provider type. Defaults to "openai" for generic OpenAI-compatible APIs.
     * @param  ?string  $wireApi  API format (openai/azure only). Defaults to "completions".
     * @param  ?string  $apiKey  API key. Optional for local providers like Ollama.
     * @param  ?string  $bearerToken  Bearer token for authentication. Sets the Authorization header directly.
     *                                Use this for services requiring bearer token auth instead of API key.
     *                                Takes precedence over apiKey when both are set.
     * @param  ?array  $azure  Azure-specific options
     */
    public function __construct(
        public string $baseUrl,
        public ?string $type = null,
        public ?string $wireApi = null,
        public ?string $apiKey = null,
        public ?string $bearerToken = null,
        public ?array $azure = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            baseUrl: $data['baseUrl'] ?? '',
            type: $data['type'] ?? null,
            wireApi: $data['wireApi'] ?? null,
            apiKey: $data['apiKey'] ?? null,
            bearerToken: $data['bearerToken'] ?? null,
            azure: $data['azure'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'baseUrl' => $this->baseUrl,
            'type' => $this->type,
            'wireApi' => $this->wireApi,
            'apiKey' => $this->apiKey,
            'bearerToken' => $this->bearerToken,
            'azure' => $this->azure,
        ], fn ($value) => $value !== null);
    }
}
