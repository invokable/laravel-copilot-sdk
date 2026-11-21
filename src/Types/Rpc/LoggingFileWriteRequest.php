<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FileLogLevel;
use Revolution\Copilot\Enums\FileLogOperation;

/** @experimental */
readonly class LoggingFileWriteRequest implements Arrayable
{
    public function __construct(
        public string $logId,
        public FileLogOperation $operation,
        public string $message,
        public ?FileLogLevel $groupLevel = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            logId: Arr::string($data, 'logId', ''),
            operation: FileLogOperation::from(Arr::string($data, 'operation', '')),
            message: Arr::string($data, 'message', ''),
            groupLevel: isset($data['groupLevel'])
                ? FileLogLevel::from(Arr::string($data, 'groupLevel'))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'logId' => $this->logId,
            'operation' => $this->operation->value,
            'message' => $this->message,
            'groupLevel' => $this->groupLevel?->value,
        ], static fn ($value): bool => $value !== null);
    }
}
