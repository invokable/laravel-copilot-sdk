<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIServiceLogEntry implements Arrayable
{
    public function __construct(
        public int|float $timestamp,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        $timestamp = $data['timestamp'] ?? 0;

        return new self(
            timestamp: is_int($timestamp) || is_float($timestamp) ? $timestamp : 0,
            message: Arr::string($data, 'message', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'message' => $this->message,
        ];
    }
}
