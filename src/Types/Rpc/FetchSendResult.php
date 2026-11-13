<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FetchResponseMode;

/** @experimental */
readonly class FetchSendResult implements Arrayable
{
    /**
     * @param  FetchHeader[]  $headers
     */
    public function __construct(
        public FetchResponseMode $responseMode,
        public string $url,
        public int $status,
        public string $statusText,
        public array $headers,
        public ?string $bodyText = null,
        public ?string $path = null,
        public ?int $sizeBytes = null,
        public ?bool $endedAtUnverifiedEof = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            responseMode: FetchResponseMode::from(Arr::string($data, 'responseMode', '')),
            url: Arr::string($data, 'url', ''),
            status: Arr::integer($data, 'status', 0),
            statusText: Arr::string($data, 'statusText', ''),
            headers: array_map(
                static fn (array $header): FetchHeader => FetchHeader::fromArray($header),
                Arr::array($data, 'headers', []),
            ),
            bodyText: isset($data['bodyText']) ? Arr::string($data, 'bodyText') : null,
            path: isset($data['path']) ? Arr::string($data, 'path') : null,
            sizeBytes: isset($data['sizeBytes']) ? Arr::integer($data, 'sizeBytes') : null,
            endedAtUnverifiedEof: isset($data['endedAtUnverifiedEof'])
                ? Arr::boolean($data, 'endedAtUnverifiedEof')
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'responseMode' => $this->responseMode->value,
            'url' => $this->url,
            'status' => $this->status,
            'statusText' => $this->statusText,
            'headers' => array_map(static fn (FetchHeader $header): array => $header->toArray(), $this->headers),
            'bodyText' => $this->bodyText,
            'path' => $this->path,
            'sizeBytes' => $this->sizeBytes,
            'endedAtUnverifiedEof' => $this->endedAtUnverifiedEof,
        ], static fn ($value): bool => $value !== null);
    }
}
