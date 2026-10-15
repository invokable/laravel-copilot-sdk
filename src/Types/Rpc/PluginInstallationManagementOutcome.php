<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\PluginInstallationFailureReason;
use Revolution\Copilot\Enums\PluginInstallationManagementOutcomeKind;

/**
 * Outcome of Agent Plugin installation planning.
 *
 * @experimental
 */
readonly class PluginInstallationManagementOutcome implements Arrayable
{
    public function __construct(
        public PluginInstallationManagementOutcomeKind $kind,
        public ?PluginInstallPlan $plan = null,
        public ?PluginInstallationFailureReason $reason = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: PluginInstallationManagementOutcomeKind::from($data['kind']),
            plan: isset($data['plan']) ? PluginInstallPlan::fromArray($data['plan']) : null,
            reason: isset($data['reason']) ? PluginInstallationFailureReason::from($data['reason']) : null,
        );
    }

    public function toArray(): array
    {
        $data = ['kind' => $this->kind->value];

        if ($this->plan !== null) {
            $data['plan'] = $this->plan->toArray();
        }
        if ($this->reason !== null) {
            $data['reason'] = $this->reason->value;
        }

        return $data;
    }
}
