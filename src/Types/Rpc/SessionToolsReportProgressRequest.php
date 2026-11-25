<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class SessionToolsReportProgressRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId'),
            message: Arr::string($data, 'message'),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'message' => $this->message,
        ];
    }
}
