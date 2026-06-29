<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A request body chunk or cancellation signal.
 */
readonly class LlmInferenceHTTPRequestChunkRequest implements Arrayable
{
    /**
     * @param  string  $data  Body byte range. UTF-8 text when `binary` is absent or false; base64-encoded when `binary` is true.
     * @param  string  $requestId  Matches the requestId from the originating httpRequestStart frame.
     * @param  ?bool  $binary  When true, `data` is base64-encoded bytes.
     * @param  ?bool  $cancel  When true, the runtime is cancelling the in-flight request.
     * @param  ?string  $cancelReason  Optional human-readable reason for the cancellation.
     * @param  ?bool  $end  When true, this is the final body chunk for the request.
     */
    public function __construct(
        public string $data,
        public string $requestId,
        public ?bool $binary = null,
        public ?bool $cancel = null,
        public ?string $cancelReason = null,
        public ?bool $end = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            data: Arr::string($data, 'data'),
            requestId: Arr::string($data, 'requestId'),
            binary: $data['binary'] ?? null,
            cancel: $data['cancel'] ?? null,
            cancelReason: $data['cancelReason'] ?? null,
            end: $data['end'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'data' => $this->data,
            'requestId' => $this->requestId,
            'binary' => $this->binary,
            'cancel' => $this->cancel,
            'cancelReason' => $this->cancelReason,
            'end' => $this->end,
        ], fn ($v) => $v !== null);
    }
}
