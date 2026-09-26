<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Network rules merged into the base sandbox policy.
 *
 * @experimental
 */
readonly class SandboxConfigUserPolicyNetwork implements Arrayable
{
    public function __construct(
        public ?array $allowedHosts = null,
        public ?array $blockedHosts = null,
        public ?bool $allowOutbound = null,
        public ?bool $allowLocalNetwork = null,
        public SandboxConfigUserPolicyNetworkProxy|array|null $proxy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $proxy = $data['proxy'] ?? null;

        return new self(
            allowedHosts: $data['allowedHosts'] ?? null,
            blockedHosts: $data['blockedHosts'] ?? null,
            allowOutbound: $data['allowOutbound'] ?? null,
            allowLocalNetwork: $data['allowLocalNetwork'] ?? null,
            proxy: $proxy !== null && ! $proxy instanceof SandboxConfigUserPolicyNetworkProxy
                ? SandboxConfigUserPolicyNetworkProxy::fromArray($proxy)
                : $proxy,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'allowedHosts' => $this->allowedHosts,
            'blockedHosts' => $this->blockedHosts,
            'allowOutbound' => $this->allowOutbound,
            'allowLocalNetwork' => $this->allowLocalNetwork,
            'proxy' => $this->proxy instanceof SandboxConfigUserPolicyNetworkProxy
                ? $this->proxy->toArray()
                : $this->proxy,
        ], fn ($value) => $value !== null);
    }
}
