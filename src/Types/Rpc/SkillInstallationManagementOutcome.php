<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SkillInstallationFailureReason;
use Revolution\Copilot\Enums\SkillInstallationManagementOutcomeKind;

/**
 * Outcome of a skill installation management operation.
 *
 * @experimental
 */
readonly class SkillInstallationManagementOutcome implements Arrayable
{
    /**
     * @param  array<string, mixed>  $data  Additional variant-specific payload.
     * @param  array<string, mixed>|null  $plan
     */
    public function __construct(
        public SkillInstallationManagementOutcomeKind $kind,
        public ?SkillInstallationFailureReason $reason = null,
        public ?int $retryAfterSeconds = null,
        public ?array $plan = null,
        public array $data = [],
    ) {}

    public static function fromArray(array $data): static
    {
        $payload = $data;
        unset($payload['kind'], $payload['reason'], $payload['retryAfterSeconds'], $payload['plan']);

        return new static(
            kind: SkillInstallationManagementOutcomeKind::from($data['kind']),
            reason: isset($data['reason']) ? SkillInstallationFailureReason::from($data['reason']) : null,
            retryAfterSeconds: isset($data['retryAfterSeconds']) ? (int) $data['retryAfterSeconds'] : null,
            plan: $data['plan'] ?? null,
            data: $payload,
        );
    }

    public function toArray(): array
    {
        $data = ['kind' => $this->kind->value];

        if ($this->plan !== null) {
            $data['plan'] = $this->plan;
        }

        foreach ($this->data as $key => $value) {
            if (in_array($key, ['kind', 'plan', 'reason', 'retryAfterSeconds'], true)) {
                continue;
            }

            $data[$key] = $value;
        }

        if ($this->reason !== null) {
            $data['reason'] = $this->reason->value;
        }
        if ($this->retryAfterSeconds !== null) {
            $data['retryAfterSeconds'] = $this->retryAfterSeconds;
        }

        return $data;
    }
}
