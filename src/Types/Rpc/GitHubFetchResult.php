<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubFetchResultKind;

/** @experimental */
readonly class GitHubFetchResult implements Arrayable
{
    /**
     * @param  GitHubFetchHeader[]|null  $headers
     */
    public function __construct(
        public GitHubFetchResultKind $kind,
        public ?string $bodyBase64 = null,
        public ?array $headers = null,
        public ?GitHubFetchHttpError $httpError = null,
        public ?string $host = null,
        public ?string $message = null,
        public ?int $status = null,
        public ?string $statusText = null,
        public ?int $timeUntilRetryMs = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: GitHubFetchResultKind::from(Arr::string($data, 'kind', '')),
            bodyBase64: isset($data['bodyBase64']) ? Arr::string($data, 'bodyBase64') : null,
            headers: isset($data['headers'])
                ? array_map(
                    static fn (array $header): GitHubFetchHeader => GitHubFetchHeader::fromArray($header),
                    Arr::array($data, 'headers'),
                )
                : null,
            httpError: isset($data['httpError']) ? GitHubFetchHttpError::fromArray($data['httpError']) : null,
            host: isset($data['host']) ? Arr::string($data, 'host') : null,
            message: isset($data['message']) ? Arr::string($data, 'message') : null,
            status: isset($data['status']) ? Arr::integer($data, 'status') : null,
            statusText: isset($data['statusText']) ? Arr::string($data, 'statusText') : null,
            timeUntilRetryMs: isset($data['timeUntilRetryMs']) ? Arr::integer($data, 'timeUntilRetryMs') : null,
            url: isset($data['url']) ? Arr::string($data, 'url') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind->value,
            'bodyBase64' => $this->bodyBase64,
            'headers' => $this->headers === null
                ? null
                : array_map(static fn (GitHubFetchHeader $header): array => $header->toArray(), $this->headers),
            'httpError' => $this->httpError?->toArray(),
            'host' => $this->host,
            'message' => $this->message,
            'status' => $this->status,
            'statusText' => $this->statusText,
            'timeUntilRetryMs' => $this->timeUntilRetryMs,
            'url' => $this->url,
        ], static fn ($value): bool => $value !== null);
    }
}
