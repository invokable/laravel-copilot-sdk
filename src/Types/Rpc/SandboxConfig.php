<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Per-session sandbox configuration.
 *
 * @experimental
 */
readonly class SandboxConfig implements Arrayable
{
    public function __construct(
        public bool $enabled,
        public SandboxConfigUserPolicy|array|null $userPolicy = null,
        public ?bool $addCurrentWorkingDirectory = null,
        public ?bool $sandboxMcpServers = null,
        public ?bool $sandboxLspServers = null,
        public ?bool $allowBypass = null,
        public ?bool $managedMcpRoutingLocked = null,
        public ?bool $managedLspRoutingLocked = null,
        public SandboxConfigAuth|array|null $auth = null,
        public SandboxCredentialsConfig|array|null $credentials = null,
        public ?bool $allowDevToolAccess = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $userPolicy = $data['userPolicy'] ?? null;
        $auth = $data['auth'] ?? null;
        $credentials = $data['credentials'] ?? null;

        return new self(
            enabled: Arr::boolean($data, 'enabled'),
            userPolicy: $userPolicy !== null && ! $userPolicy instanceof SandboxConfigUserPolicy
                ? SandboxConfigUserPolicy::fromArray($userPolicy)
                : $userPolicy,
            addCurrentWorkingDirectory: $data['addCurrentWorkingDirectory'] ?? null,
            sandboxMcpServers: $data['sandboxMcpServers'] ?? null,
            sandboxLspServers: $data['sandboxLspServers'] ?? null,
            allowBypass: $data['allowBypass'] ?? null,
            managedMcpRoutingLocked: $data['managedMcpRoutingLocked'] ?? null,
            managedLspRoutingLocked: $data['managedLspRoutingLocked'] ?? null,
            auth: $auth !== null && ! $auth instanceof SandboxConfigAuth
                ? SandboxConfigAuth::fromArray($auth)
                : $auth,
            credentials: $credentials !== null && ! $credentials instanceof SandboxCredentialsConfig
                ? SandboxCredentialsConfig::fromArray($credentials)
                : $credentials,
            allowDevToolAccess: $data['allowDevToolAccess'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'userPolicy' => $this->userPolicy instanceof SandboxConfigUserPolicy
                ? $this->userPolicy->toArray()
                : $this->userPolicy,
            'addCurrentWorkingDirectory' => $this->addCurrentWorkingDirectory,
            'sandboxMcpServers' => $this->sandboxMcpServers,
            'sandboxLspServers' => $this->sandboxLspServers,
            'allowBypass' => $this->allowBypass,
            'managedMcpRoutingLocked' => $this->managedMcpRoutingLocked,
            'managedLspRoutingLocked' => $this->managedLspRoutingLocked,
            'auth' => $this->auth instanceof SandboxConfigAuth
                ? $this->auth->toArray()
                : $this->auth,
            'credentials' => $this->credentials instanceof SandboxCredentialsConfig
                ? $this->credentials->toArray()
                : $this->credentials,
            'allowDevToolAccess' => $this->allowDevToolAccess,
        ], fn ($value) => $value !== null);
    }
}
