<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class LoggingFileOpenResult implements Arrayable
{
    public function __construct(
        public string $logId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            logId: Arr::string($data, 'logId', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'logId' => $this->logId,
        ];
    }
}
