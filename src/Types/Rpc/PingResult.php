<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of a ping request.
 */
readonly class PingResult implements Arrayable
{
    public function __construct(
        /** Echoed message (or default greeting) */
        public string $message,
        /** Server timestamp in milliseconds */
        public float $timestamp,
        /** Server protocol version number */
        public float $protocolVersion,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            timestamp: $data['timestamp'],
            protocolVersion: $data['protocolVersion'],
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'timestamp' => $this->timestamp,
            'protocolVersion' => $this->protocolVersion,
        ];
    }
}
