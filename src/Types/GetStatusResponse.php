<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Response from status.get.
 */
readonly class GetStatusResponse implements Arrayable
{
    /**
     * @param  string  $version  Package version (e.g., "1.0.0")
     * @param  int  $protocolVersion  Protocol version for SDK compatibility
     */
    public function __construct(
        public string $version,
        public int $protocolVersion,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{version: string, protocolVersion: int}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: $data['version'],
            protocolVersion: $data['protocolVersion'],
        );
    }

    /**
     * Convert to array.
     *
     * @return array{version: string, protocolVersion: int}
     */
    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'protocolVersion' => $this->protocolVersion,
        ];
    }
}
