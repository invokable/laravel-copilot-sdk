<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Platform-specific experimental sandbox policy fields.
 *
 * @experimental
 */
readonly class SandboxConfigUserPolicyExperimental implements Arrayable
{
    public function __construct(
        public SandboxConfigUserPolicyExperimentalSeatbelt|array|null $seatbelt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $seatbelt = $data['seatbelt'] ?? null;

        return new self(
            seatbelt: $seatbelt !== null && ! $seatbelt instanceof SandboxConfigUserPolicyExperimentalSeatbelt
                ? SandboxConfigUserPolicyExperimentalSeatbelt::fromArray($seatbelt)
                : $seatbelt,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'seatbelt' => $this->seatbelt instanceof SandboxConfigUserPolicyExperimentalSeatbelt
                ? $this->seatbelt->toArray()
                : $this->seatbelt,
        ], fn ($value) => $value !== null);
    }
}
