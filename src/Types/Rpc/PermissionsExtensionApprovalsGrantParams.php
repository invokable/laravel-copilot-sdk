<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class PermissionsExtensionApprovalsGrantParams implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public string $requestId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            requestId: Arr::string($data, 'requestId'),
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'requestId' => $this->requestId,
        ];
    }
}
