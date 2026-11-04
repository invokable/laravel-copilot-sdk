<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubFetchRequest implements Arrayable
{
    /**
     * @param  GitHubFetchHeader[]  $headers
     * @param  GitHubFetchPinnedAddress[]|null  $pinnedAddresses
     */
    public function __construct(
        public int $requestId,
        public string $method,
        public string $url,
        public array $headers,
        public string $apiName,
        public ?string $sessionId = null,
        public ?string $authOwnerId = null,
        public ?string $bodyBase64 = null,
        public ?GitHubFetchRetryConfig $retryOptions = null,
        public ?string $redirect = null,
        public ?int $timeoutMs = null,
        public ?int $readTimeoutMs = null,
        public ?int $bodyLimitBytes = null,
        public ?bool $disableConnectionReuse = null,
        public ?GitHubFetchProxyEnvironment $proxyEnv = null,
        public ?array $pinnedAddresses = null,
        public ?bool $allowLocalhost = null,
        public ?bool $proxyNegotiateAuth = null,
        public ?string $proxyNegotiateSpn = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::integer($data, 'requestId', 0),
            method: Arr::string($data, 'method', ''),
            url: Arr::string($data, 'url', ''),
            headers: array_map(
                static fn (array $header): GitHubFetchHeader => GitHubFetchHeader::fromArray($header),
                Arr::array($data, 'headers', []),
            ),
            apiName: Arr::string($data, 'apiName', ''),
            sessionId: isset($data['sessionId']) ? Arr::string($data, 'sessionId') : null,
            authOwnerId: isset($data['authOwnerId']) ? Arr::string($data, 'authOwnerId') : null,
            bodyBase64: isset($data['bodyBase64']) ? Arr::string($data, 'bodyBase64') : null,
            retryOptions: isset($data['retryOptions']) ? GitHubFetchRetryConfig::fromArray($data['retryOptions']) : null,
            redirect: isset($data['redirect']) ? Arr::string($data, 'redirect') : null,
            timeoutMs: isset($data['timeoutMs']) ? Arr::integer($data, 'timeoutMs') : null,
            readTimeoutMs: isset($data['readTimeoutMs']) ? Arr::integer($data, 'readTimeoutMs') : null,
            bodyLimitBytes: isset($data['bodyLimitBytes']) ? Arr::integer($data, 'bodyLimitBytes') : null,
            disableConnectionReuse: isset($data['disableConnectionReuse']) ? Arr::boolean($data, 'disableConnectionReuse') : null,
            proxyEnv: isset($data['proxyEnv']) ? GitHubFetchProxyEnvironment::fromArray($data['proxyEnv']) : null,
            pinnedAddresses: isset($data['pinnedAddresses'])
                ? array_map(
                    static fn (array $address): GitHubFetchPinnedAddress => GitHubFetchPinnedAddress::fromArray($address),
                    Arr::array($data, 'pinnedAddresses'),
                )
                : null,
            allowLocalhost: isset($data['allowLocalhost']) ? Arr::boolean($data, 'allowLocalhost') : null,
            proxyNegotiateAuth: isset($data['proxyNegotiateAuth']) ? Arr::boolean($data, 'proxyNegotiateAuth') : null,
            proxyNegotiateSpn: isset($data['proxyNegotiateSpn']) ? Arr::string($data, 'proxyNegotiateSpn') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'sessionId' => $this->sessionId,
            'authOwnerId' => $this->authOwnerId,
            'method' => $this->method,
            'url' => $this->url,
            'headers' => array_map(static fn (GitHubFetchHeader $header): array => $header->toArray(), $this->headers),
            'bodyBase64' => $this->bodyBase64,
            'apiName' => $this->apiName,
            'retryOptions' => $this->retryOptions?->toArray(),
            'redirect' => $this->redirect,
            'timeoutMs' => $this->timeoutMs,
            'readTimeoutMs' => $this->readTimeoutMs,
            'bodyLimitBytes' => $this->bodyLimitBytes,
            'disableConnectionReuse' => $this->disableConnectionReuse,
            'proxyEnv' => $this->proxyEnv?->toArray(),
            'pinnedAddresses' => $this->pinnedAddresses === null
                ? null
                : array_map(static fn (GitHubFetchPinnedAddress $address): array => $address->toArray(), $this->pinnedAddresses),
            'allowLocalhost' => $this->allowLocalhost,
            'proxyNegotiateAuth' => $this->proxyNegotiateAuth,
            'proxyNegotiateSpn' => $this->proxyNegotiateSpn,
        ], static fn ($value): bool => $value !== null);
    }
}
