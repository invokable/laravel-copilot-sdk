<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogReferenceInstallationFailureReason;
use Revolution\Copilot\Enums\CatalogReferenceInstallationOutcomeKind;

/**
 * Planning outcome naming the resource kind for a materialisation reference.
 *
 * @experimental
 */
readonly class CatalogReferenceInstallationOutcome implements Arrayable
{
    /**
     * @param  PluginInstallPlan|array<string, mixed>|null  $plan
     */
    public function __construct(
        public CatalogReferenceInstallationOutcomeKind $kind,
        public PluginInstallPlan|array|null $plan = null,
        public ?CatalogReferenceInstallationFailureReason $reason = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $kind = CatalogReferenceInstallationOutcomeKind::from($data['kind']);
        $plan = $data['plan'] ?? null;

        if ($kind === CatalogReferenceInstallationOutcomeKind::PluginInstallPlanned && $plan !== null) {
            $plan = PluginInstallPlan::fromArray($plan);
        }

        return new static(
            kind: $kind,
            plan: $plan,
            reason: isset($data['reason']) ? CatalogReferenceInstallationFailureReason::from($data['reason']) : null,
        );
    }

    public function toArray(): array
    {
        $data = ['kind' => $this->kind->value];

        if ($this->plan !== null) {
            $data['plan'] = $this->plan instanceof PluginInstallPlan
                ? $this->plan->toArray()
                : $this->plan;
        }
        if ($this->reason !== null) {
            $data['reason'] = $this->reason->value;
        }

        return $data;
    }
}
