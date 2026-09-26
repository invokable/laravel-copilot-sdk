<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * User-managed sandbox policy fragment.
 *
 * @experimental
 */
readonly class SandboxConfigUserPolicy implements Arrayable
{
    public function __construct(
        public SandboxConfigUserPolicyFilesystem|array|null $filesystem = null,
        public SandboxConfigUserPolicyNetwork|array|null $network = null,
        public SandboxConfigUserPolicySeatbelt|array|null $seatbelt = null,
        public ?array $experimental = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $filesystem = $data['filesystem'] ?? null;
        $network = $data['network'] ?? null;
        $seatbelt = $data['seatbelt'] ?? null;

        return new self(
            filesystem: $filesystem !== null && ! $filesystem instanceof SandboxConfigUserPolicyFilesystem
                ? SandboxConfigUserPolicyFilesystem::fromArray($filesystem)
                : $filesystem,
            network: $network !== null && ! $network instanceof SandboxConfigUserPolicyNetwork
                ? SandboxConfigUserPolicyNetwork::fromArray($network)
                : $network,
            seatbelt: $seatbelt !== null && ! $seatbelt instanceof SandboxConfigUserPolicySeatbelt
                ? SandboxConfigUserPolicySeatbelt::fromArray($seatbelt)
                : $seatbelt,
            experimental: $data['experimental'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'filesystem' => $this->filesystem instanceof SandboxConfigUserPolicyFilesystem
                ? $this->filesystem->toArray()
                : $this->filesystem,
            'network' => $this->network instanceof SandboxConfigUserPolicyNetwork
                ? $this->network->toArray()
                : $this->network,
            'seatbelt' => $this->seatbelt instanceof SandboxConfigUserPolicySeatbelt
                ? $this->seatbelt->toArray()
                : $this->seatbelt,
            'experimental' => $this->experimental,
        ], fn ($value) => $value !== null);
    }
}
