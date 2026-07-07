<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Connection handshake request.
 *
 * @internal This type is part of the SDK's internal surface.
 */
readonly class ConnectRequest implements Arrayable
{
    /**
     * @param  ?bool  $enableGitHubTelemetryForwarding  Opt this connection in to GitHub telemetry forwarding.
     * @param  ?string  $token  Connection token; required when the server was started with COPILOT_CONNECTION_TOKEN
     */
    public function __construct(
        public ?bool $enableGitHubTelemetryForwarding = null,
        public ?string $token = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            enableGitHubTelemetryForwarding: isset($data['enableGitHubTelemetryForwarding']) ? (bool) $data['enableGitHubTelemetryForwarding'] : null,
            token: $data['token'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enableGitHubTelemetryForwarding' => $this->enableGitHubTelemetryForwarding,
            'token' => $this->token,
        ], fn ($value) => $value !== null);
    }
}
