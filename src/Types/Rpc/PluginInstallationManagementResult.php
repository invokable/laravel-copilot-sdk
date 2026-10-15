<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogInvalidRequestField;
use Revolution\Copilot\Enums\CatalogNegotiationRefusedReason;
use Revolution\Copilot\Enums\CatalogReferenceInstallationResultKind;

/**
 * Result of Agent Plugin installation planning with its negotiated contract or refusal.
 *
 * @experimental
 */
readonly class PluginInstallationManagementResult implements Arrayable
{
    /**
     * @param  string[]|null  $supportedCapabilities
     * @param  string[]|null  $unsupportedCapabilities
     */
    public function __construct(
        public CatalogReferenceInstallationResultKind $kind,
        public ?CatalogNegotiatedContract $negotiated = null,
        public ?PluginInstallationManagementOutcome $outcome = null,
        public ?string $message = null,
        public ?int $minimumSupportedProtocolVersion = null,
        public ?CatalogNegotiationRefusedReason $reason = null,
        public ?int $runtimeProtocolVersion = null,
        public ?array $supportedCapabilities = null,
        public ?array $unsupportedCapabilities = null,
        public ?CatalogInvalidRequestField $field = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: CatalogReferenceInstallationResultKind::from($data['kind']),
            negotiated: isset($data['negotiated']) ? CatalogNegotiatedContract::fromArray($data['negotiated']) : null,
            outcome: isset($data['outcome']) ? PluginInstallationManagementOutcome::fromArray($data['outcome']) : null,
            message: $data['message'] ?? null,
            minimumSupportedProtocolVersion: $data['minimumSupportedProtocolVersion'] ?? null,
            reason: isset($data['reason']) ? CatalogNegotiationRefusedReason::from($data['reason']) : null,
            runtimeProtocolVersion: $data['runtimeProtocolVersion'] ?? null,
            supportedCapabilities: $data['supportedCapabilities'] ?? null,
            unsupportedCapabilities: $data['unsupportedCapabilities'] ?? null,
            field: isset($data['field']) ? CatalogInvalidRequestField::from($data['field']) : null,
        );
    }

    public function toArray(): array
    {
        $data = ['kind' => $this->kind->value];

        foreach ([
            'negotiated' => $this->negotiated?->toArray(),
            'outcome' => $this->outcome?->toArray(),
            'message' => $this->message,
            'minimumSupportedProtocolVersion' => $this->minimumSupportedProtocolVersion,
            'reason' => $this->reason?->value,
            'runtimeProtocolVersion' => $this->runtimeProtocolVersion,
            'supportedCapabilities' => $this->supportedCapabilities,
            'unsupportedCapabilities' => $this->unsupportedCapabilities,
            'field' => $this->field?->value,
        ] as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
