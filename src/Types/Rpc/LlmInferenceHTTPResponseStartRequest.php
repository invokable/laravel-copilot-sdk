<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Response head (status + headers) for an in-flight request.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class LlmInferenceHTTPResponseStartRequest implements Arrayable
{
    /**
     * @param  array<string, list<string>>  $headers
     * @param  string  $requestId  Matches the requestId from the originating httpRequestStart frame.
     * @param  int  $status  HTTP status code.
     * @param  ?string  $statusText  Optional HTTP status reason phrase.
     */
    public function __construct(
        public array $headers,
        public string $requestId,
        public int $status,
        public ?string $statusText = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            headers: $data['headers'],
            requestId: $data['requestId'],
            status: $data['status'],
            statusText: $data['statusText'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'headers' => $this->headers,
            'requestId' => $this->requestId,
            'status' => $this->status,
            'statusText' => $this->statusText,
        ], fn ($v) => $v !== null);
    }
}
