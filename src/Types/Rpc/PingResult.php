<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of a ping request.
 */
readonly class PingResult implements Arrayable
{
    /**
     * @param  string  $message  Echoed message (or default greeting)
     * @param  float  $timestamp  Server timestamp in milliseconds
     * @param  float  $protocolVersion  Server protocol version number
     */
    public function __construct(
        public string $message,
        public float $timestamp,
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
