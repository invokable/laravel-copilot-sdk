<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use SensitiveParameter;

/**
 * Configuration for a custom API provider.
 */
readonly class ProviderConfig implements Arrayable
{
    public ?Closure $bearerTokenProvider;

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
     * @param  ?bool  $hasBearerTokenProvider  When true, the SDK client supplies bearer tokens on demand via a callback.
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
     * @param  (callable(array{providerName: string, sessionId: string}): string)|null  $bearerTokenProvider  Callback used to acquire bearer tokens on demand.
     *                                                                                                        Takes precedence over apiKey, apiKeyCommand, and bearerToken.
     * @param  ?string  $apiKeyCommand  Command run by the Copilot runtime (not by this package) before each provider
     *                                  request to obtain a fresh API key. A failing command fails the turn before any
     *                                  model request is sent. Must be a non-blank string; the command and its output
     *                                  are treated as secrets and never included in exception messages. Does not run
     *                                  when bearerTokenProvider is set.
     *
     * @throws InvalidArgumentException When apiKeyCommand is an empty or blank string.
     */
    public function __construct(
        public string $baseUrl,
        public ?string $type = null,
        public ?string $wireApi = null,
        public ?string $transport = null,
        public ?string $apiKey = null,
        public ?string $bearerToken = null,
        public ?bool $hasBearerTokenProvider = null,
        public ?array $azure = null,
        public ?array $headers = null,
        public ?string $modelId = null,
        public ?string $wireModel = null,
        public ?int $maxPromptTokens = null,
        public ?int $maxOutputTokens = null,
        public ?string $modelProvider = null,
        ?callable $bearerTokenProvider = null,
        #[SensitiveParameter]
        public ?string $apiKeyCommand = null,
    ) {
        self::assertValidApiKeyCommand($apiKeyCommand);

        $this->bearerTokenProvider = $bearerTokenProvider === null
            ? null
            : Closure::fromCallable($bearerTokenProvider);
    }

    /**
     * Validate a provider apiKeyCommand value without ever echoing it.
     *
     * @throws InvalidArgumentException When the value is not null and not a non-blank string.
     */
    public static function assertValidApiKeyCommand(#[SensitiveParameter] mixed $command): void
    {
        if ($command === null) {
            return;
        }

        if (! is_string($command) || trim($command) === '') {
            throw new InvalidArgumentException('provider.apiKeyCommand must be a non-empty string.');
        }
    }

    /**
     * Create from array data.
     */
    public static function fromArray(#[SensitiveParameter] array $data): self
    {
        self::assertValidApiKeyCommand($data['apiKeyCommand'] ?? null);

        return new self(
            baseUrl: Arr::string($data, 'baseUrl', ''),
            type: $data['type'] ?? null,
            wireApi: $data['wireApi'] ?? null,
            transport: $data['transport'] ?? null,
            apiKey: $data['apiKey'] ?? null,
            bearerToken: $data['bearerToken'] ?? null,
            hasBearerTokenProvider: $data['hasBearerTokenProvider'] ?? null,
            bearerTokenProvider: $data['bearerTokenProvider'] ?? null,
            azure: $data['azure'] ?? null,
            headers: $data['headers'] ?? null,
            modelId: $data['modelId'] ?? null,
            wireModel: $data['wireModel'] ?? null,
            maxPromptTokens: $data['maxPromptTokens'] ?? $data['maxInputTokens'] ?? null,
            maxOutputTokens: $data['maxOutputTokens'] ?? null,
            modelProvider: $data['modelProvider'] ?? null,
            apiKeyCommand: $data['apiKeyCommand'] ?? null,
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
            'apiKeyCommand' => $this->apiKeyCommand,
            'bearerToken' => $this->bearerToken,
            'hasBearerTokenProvider' => $this->hasBearerTokenProvider,
            'azure' => $this->azure,
            'headers' => $this->headers,
            'modelId' => $this->modelId,
            'wireModel' => $this->wireModel,
            'maxPromptTokens' => $this->maxPromptTokens,
            'maxOutputTokens' => $this->maxOutputTokens,
            'modelProvider' => $this->modelProvider,
        ], fn ($value) => $value !== null);
    }
}
