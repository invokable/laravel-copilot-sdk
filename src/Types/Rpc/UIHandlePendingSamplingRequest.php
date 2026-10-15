<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for handling a pending sampling request.
 */
readonly class UIHandlePendingSamplingRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public ?array $response = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId'),
            response: isset($data['response']) ? Arr::array($data, 'response') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'response' => $this->response,
        ], fn ($value) => $value !== null);
    }
}
