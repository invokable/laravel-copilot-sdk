<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
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
     * @param  ?string  $agentId  Stable identity of the agent that issued this request.
     * @param  ?string  $agentInvocationId  Logical agent invocation identity.
     * @param  ?string  $parentAgentId  Identity of the parent agent, when this is a subagent request.
     * @param  ?string  $interactionType  Runtime classification for the interaction.
     * @param  LlmInferenceHttpRequestStartTransport|string|null  $transport  Transport the runtime would otherwise use for this request.
     */
    public function __construct(
        public array $headers,
        public string $method,
        public string $requestId,
        public string $url,
        public ?string $sessionId = null,
        public LlmInferenceHttpRequestStartTransport|string|null $transport = null,
        public ?string $agentId = null,
        public ?string $agentInvocationId = null,
        public ?string $parentAgentId = null,
        public ?string $interactionType = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            headers: Arr::array($data, 'headers'),
            method: Arr::string($data, 'method'),
            requestId: Arr::string($data, 'requestId'),
            url: Arr::string($data, 'url'),
            sessionId: $data['sessionId'] ?? null,
            transport: isset($data['transport']) ? LlmInferenceHttpRequestStartTransport::from($data['transport']) : null,
            agentId: $data['agentId'] ?? null,
            agentInvocationId: $data['agentInvocationId'] ?? null,
            parentAgentId: $data['parentAgentId'] ?? null,
            interactionType: $data['interactionType'] ?? null,
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
            'agentId' => $this->agentId,
            'agentInvocationId' => $this->agentInvocationId,
            'parentAgentId' => $this->parentAgentId,
            'interactionType' => $this->interactionType,
        ], fn ($v) => $v !== null);
    }
}
