<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A response body chunk or terminal error.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class LlmInferenceHTTPResponseChunkRequest implements Arrayable
{
    /**
     * @param  string  $data  Body byte range. UTF-8 text when `binary` is absent or false; base64-encoded when `binary` is true.
     * @param  string  $requestId  Matches the requestId from the originating httpRequestStart frame.
     * @param  ?bool  $binary  When true, `data` is base64-encoded bytes.
     * @param  ?bool  $end  When true, this is the final body chunk for the response.
     * @param  LlmInferenceHTTPResponseChunkError|array|null  $error  Set to terminate the response with a transport-level failure.
     */
    public function __construct(
        public string $data,
        public string $requestId,
        public ?bool $binary = null,
        public ?bool $end = null,
        public LlmInferenceHTTPResponseChunkError|array|null $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $error = null;
        if (isset($data['error'])) {
            $error = $data['error'] instanceof LlmInferenceHTTPResponseChunkError
                ? $data['error']
                : LlmInferenceHTTPResponseChunkError::fromArray($data['error']);
        }

        return new self(
            data: $data['data'],
            requestId: $data['requestId'],
            binary: $data['binary'] ?? null,
            end: $data['end'] ?? null,
            error: $error,
        );
    }

    public function toArray(): array
    {
        $error = $this->error instanceof LlmInferenceHTTPResponseChunkError
            ? $this->error->toArray()
            : $this->error;

        return array_filter([
            'data' => $this->data,
            'requestId' => $this->requestId,
            'binary' => $this->binary,
            'end' => $this->end,
            'error' => $error,
        ], fn ($v) => $v !== null);
    }
}
