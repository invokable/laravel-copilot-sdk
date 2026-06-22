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
     *                            Set to "responses" to use the OpenAI Responses API format.
     * @param  ?string  $transport  Transport for OpenAI Responses requests. Defaults to "http".
     *                              Set to "websockets" to deliver Responses API requests over a persistent
     *                              WebSocket connection. Applies to OpenAI-compatible providers using
     *                              `wireApi: "responses"`.
     * @param  ?string  $apiKey  API key. Optional for local providers like Ollama.
     * @param  ?string  $bearerToken  Bearer token for authentication. Sets the Authorization header directly.
     *                                Use this for services requiring bearer token auth instead of API key.
     *                                Takes precedence over apiKey when both are set.
     * @param  ?array  $azure  Azure-specific options
     * @param  ?array<string, string>  $headers  Custom HTTP headers to include in outbound provider requests.
     * @param  ?string  $modelId  Well-known model name used by the runtime to look up agent configuration
     *                            and default token limits. Falls back to SessionConfig::$model.
     * @param  ?string  $wireModel  Model name sent to the provider API for inference. Use when the
     *                              provider's model name differs from modelId (e.g. Azure deployment name).
     *                              Falls back to modelId, then SessionConfig::$model.
     * @param  ?int  $maxPromptTokens  Overrides the resolved model's default max prompt tokens. The runtime
     *                                 triggers conversation compaction before this limit is exceeded.
     * @param  ?int  $maxOutputTokens  Overrides the resolved model's default max output tokens.
     */
    public function __construct(
        public string $baseUrl,
        public ?string $type = null,
        public ?string $wireApi = null,
        public ?string $transport = null,
        public ?string $apiKey = null,
        public ?string $bearerToken = null,
        public ?array $azure = null,
        public ?array $headers = null,
        public ?string $modelId = null,
        public ?string $wireModel = null,
        public ?int $maxPromptTokens = null,
        public ?int $maxOutputTokens = null,
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
            transport: $data['transport'] ?? null,
            apiKey: $data['apiKey'] ?? null,
            bearerToken: $data['bearerToken'] ?? null,
            azure: $data['azure'] ?? null,
            headers: $data['headers'] ?? null,
            modelId: $data['modelId'] ?? null,
            wireModel: $data['wireModel'] ?? null,
            maxPromptTokens: $data['maxPromptTokens'] ?? $data['maxInputTokens'] ?? null,
            maxOutputTokens: $data['maxOutputTokens'] ?? null,
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
            'transport' => $this->transport,
            'apiKey' => $this->apiKey,
            'bearerToken' => $this->bearerToken,
            'azure' => $this->azure,
            'headers' => $this->headers,
            'modelId' => $this->modelId,
            'wireModel' => $this->wireModel,
            'maxPromptTokens' => $this->maxPromptTokens,
            'maxOutputTokens' => $this->maxOutputTokens,
        ], fn ($value) => $value !== null);
    }
}
