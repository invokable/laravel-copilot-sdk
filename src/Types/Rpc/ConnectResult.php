<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Connection handshake result.
 *
 * @internal This type is part of the SDK's internal surface.
 */
readonly class ConnectResult implements Arrayable
{
    /**
     * @param  bool  $ok  Always true on success
     * @param  int  $protocolVersion  Server protocol version number
     * @param  string  $version  Server package version
     */
    public function __construct(
        public bool $ok,
        public int $protocolVersion,
        public string $version,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            ok: (bool) ($data['ok'] ?? false),
            protocolVersion: (int) ($data['protocolVersion'] ?? 0),
            version: (string) ($data['version'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'protocolVersion' => $this->protocolVersion,
            'version' => $this->version,
        ];
    }
}
