<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to attach runtime-managed remote control to a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsStartRemoteControlRequest implements Arrayable
{
    public function __construct(
        public string $sessionId,
        public RemoteControlConfig|array $config,
    ) {}

    public static function fromArray(array $data): self
    {
        $config = $data['config'] ?? [];

        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            config: $config instanceof RemoteControlConfig
                ? $config
                : RemoteControlConfig::fromArray($config),
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'config' => $this->config instanceof RemoteControlConfig
                ? $this->config->toArray()
                : $this->config,
        ];
    }
}
