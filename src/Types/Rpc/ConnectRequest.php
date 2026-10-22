<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TaskKind;
use Revolution\Copilot\Types\EntraAuthConfig;

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
     * @param  array<TaskKind|string>|null  $supportedTaskKinds  Closed set of public task kinds this connection can negotiate.
     * @param  ?EntraAuthConfig  $entra  Host application's Microsoft Entra registration.
     */
    public function __construct(
        public ?bool $enableGitHubTelemetryForwarding = null,
        public ?string $token = null,
        public ?ConnectClientInfo $clientInfo = null,
        public ?array $supportedTaskKinds = null,
        public ?EntraAuthConfig $entra = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            clientInfo: isset($data['clientInfo']) ? ConnectClientInfo::fromArray($data['clientInfo']) : null,
            enableGitHubTelemetryForwarding: isset($data['enableGitHubTelemetryForwarding']) ? (bool) $data['enableGitHubTelemetryForwarding'] : null,
            token: $data['token'] ?? null,
            supportedTaskKinds: $data['supportedTaskKinds'] ?? null,
            entra: isset($data['entra']) ? EntraAuthConfig::fromArray($data['entra']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'clientInfo' => $this->clientInfo?->toArray(),
            'enableGitHubTelemetryForwarding' => $this->enableGitHubTelemetryForwarding,
            'token' => $this->token,
            'supportedTaskKinds' => $this->supportedTaskKinds !== null
                ? array_map(fn ($kind) => $kind instanceof TaskKind ? $kind->value : $kind, $this->supportedTaskKinds)
                : null,
            'entra' => $this->entra?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
