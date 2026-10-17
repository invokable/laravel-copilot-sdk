<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Identifies a connection-owned Azure ML download for cancellation.
 *
 * @experimental
 */
readonly class AzureMlCancelRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(requestId: $data['requestId'] ?? '');
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
        ];
    }
}
