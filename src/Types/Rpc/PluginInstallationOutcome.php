<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\PluginInstallationFailureReason;
use Revolution\Copilot\Enums\PluginInstallationOutcomeKind;

/**
 * Terminal outcome of a verified Agent Plugin installation.
 *
 * @experimental
 */
readonly class PluginInstallationOutcome implements Arrayable
{
    public function __construct(
        public PluginInstallationOutcomeKind $kind,
        public ?PluginInstallationSummary $installation = null,
        public ?string $operationId = null,
        public ?PluginInstallationFailureReason $reason = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: PluginInstallationOutcomeKind::from($data['kind']),
            installation: isset($data['installation']) ? PluginInstallationSummary::fromArray($data['installation']) : null,
            operationId: $data['operationId'] ?? null,
            reason: isset($data['reason']) ? PluginInstallationFailureReason::from($data['reason']) : null,
        );
    }

    public function toArray(): array
    {
        $data = ['kind' => $this->kind->value];

        if ($this->installation !== null) {
            $data['installation'] = $this->installation->toArray();
        }
        if ($this->operationId !== null) {
            $data['operationId'] = $this->operationId;
        }
        if ($this->reason !== null) {
            $data['reason'] = $this->reason->value;
        }

        return $data;
    }
}
