<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FetchRedirect;
use Revolution\Copilot\Enums\FetchResponseMode;

/** @experimental */
readonly class FetchSendParams implements Arrayable
{
    /**
     * @param  FetchHeader[]|null  $headers
     * @param  string[]|null  $pinnedAddresses
     */
    public function __construct(
        public string $requestId,
        public string $url,
        public FetchResponseMode $responseMode,
        public ?string $destinationPath = null,
        public ?string $credentialContextId = null,
        public ?string $method = null,
        public ?array $headers = null,
        public ?string $body = null,
        public ?FetchRedirect $redirect = null,
        public ?int $timeoutMs = null,
        public ?int $readTimeoutMs = null,
        public ?int $bodyLimitBytes = null,
        public ?bool $disableConnectionReuse = null,
        public ?FetchProxyEnv $proxyEnv = null,
        public ?array $pinnedAddresses = null,
        public ?bool $allowLocalhost = null,
        public ?bool $proxyNegotiateAuth = null,
        public ?string $proxyNegotiateSpn = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId', ''),
            url: Arr::string($data, 'url', ''),
            responseMode: FetchResponseMode::from(Arr::string($data, 'responseMode', '')),
            destinationPath: isset($data['destinationPath']) ? Arr::string($data, 'destinationPath') : null,
            credentialContextId: isset($data['credentialContextId']) ? Arr::string($data, 'credentialContextId') : null,
            method: isset($data['method']) ? Arr::string($data, 'method') : null,
            headers: isset($data['headers'])
                ? array_map(
                    static fn (array $header): FetchHeader => FetchHeader::fromArray($header),
                    Arr::array($data, 'headers'),
                )
                : null,
            body: isset($data['body']) ? Arr::string($data, 'body') : null,
            redirect: isset($data['redirect']) ? FetchRedirect::from(Arr::string($data, 'redirect')) : null,
            timeoutMs: isset($data['timeoutMs']) ? Arr::integer($data, 'timeoutMs') : null,
            readTimeoutMs: isset($data['readTimeoutMs']) ? Arr::integer($data, 'readTimeoutMs') : null,
            bodyLimitBytes: isset($data['bodyLimitBytes']) ? Arr::integer($data, 'bodyLimitBytes') : null,
            disableConnectionReuse: isset($data['disableConnectionReuse']) ? Arr::boolean($data, 'disableConnectionReuse') : null,
            proxyEnv: isset($data['proxyEnv']) ? FetchProxyEnv::fromArray($data['proxyEnv']) : null,
            pinnedAddresses: isset($data['pinnedAddresses'])
                ? array_map(static fn (string $address): string => $address, Arr::array($data, 'pinnedAddresses'))
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
            'url' => $this->url,
            'responseMode' => $this->responseMode->value,
            'destinationPath' => $this->destinationPath,
            'credentialContextId' => $this->credentialContextId,
            'method' => $this->method,
            'headers' => $this->headers === null
                ? null
                : array_map(static fn (FetchHeader $header): array => $header->toArray(), $this->headers),
            'body' => $this->body,
            'redirect' => $this->redirect?->value,
            'timeoutMs' => $this->timeoutMs,
            'readTimeoutMs' => $this->readTimeoutMs,
            'bodyLimitBytes' => $this->bodyLimitBytes,
            'disableConnectionReuse' => $this->disableConnectionReuse,
            'proxyEnv' => $this->proxyEnv?->toArray(),
            'pinnedAddresses' => $this->pinnedAddresses,
            'allowLocalhost' => $this->allowLocalhost,
            'proxyNegotiateAuth' => $this->proxyNegotiateAuth,
            'proxyNegotiateSpn' => $this->proxyNegotiateSpn,
        ], static fn ($value): bool => $value !== null);
    }
}
