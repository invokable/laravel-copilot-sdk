<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\LlmInferenceHttpRequestStartTransport;

/**
 * The head of an outbound model-layer HTTP request.
 */
readonly class LlmInferenceHTTPRequestStartRequest implements Arrayable
{
    /**
     * @param  array<string, list<string>>  $headers
     * @param  string  $method  HTTP method, e.g. GET, POST.
     * @param  string  $requestId  Opaque runtime-minted id, unique per in-flight request.
     * @param  string  $url  Absolute request URL.
     * @param  ?string  $sessionId  Id of the runtime session that triggered this request, when one is in scope.
     * @param  LlmInferenceHttpRequestStartTransport|string|null  $transport  Transport the runtime would otherwise use for this request.
     */
    public function __construct(
        public array $headers,
        public string $method,
        public string $requestId,
        public string $url,
        public ?string $sessionId = null,
        public LlmInferenceHttpRequestStartTransport|string|null $transport = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            headers: $data['headers'],
            method: $data['method'],
            requestId: $data['requestId'],
            url: $data['url'],
            sessionId: $data['sessionId'] ?? null,
            transport: isset($data['transport']) ? LlmInferenceHttpRequestStartTransport::from($data['transport']) : null,
        );
    }

    public function toArray(): array
    {
        $transport = $this->transport instanceof LlmInferenceHttpRequestStartTransport
            ? $this->transport->value
            : $this->transport;

        return array_filter([
            'headers' => $this->headers,
            'method' => $this->method,
            'requestId' => $this->requestId,
            'url' => $this->url,
            'sessionId' => $this->sessionId,
            'transport' => $transport,
        ], fn ($v) => $v !== null);
    }
}
