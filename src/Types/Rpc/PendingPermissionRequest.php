<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Pending permission request reconstructed from session event history.
 */
readonly class PendingPermissionRequest implements Arrayable
{
    /**
     * @param  string  $requestId  Unique identifier for the pending permission request
     * @param  array  $request  Permission prompt payload
     */
    public function __construct(
        public string $requestId,
        public array $request,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId'),
            request: Arr::array($data, 'request'),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'request' => $this->request,
        ];
    }
}
