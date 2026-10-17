<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogInvalidRequestField;
use Revolution\Copilot\Enums\CatalogNegotiationRefusedReason;
use Revolution\Copilot\Enums\CatalogNetworkFailureReason;
use Revolution\Copilot\Enums\SkillInstallationManagementResultKind;

/**
 * Skill installation management result with the honoured contract or a typed refusal.
 *
 * @experimental
 */
readonly class SkillInstallationManagementResult implements Arrayable
{
    /**
     * @param  string[]|null  $supportedCapabilities
     * @param  string[]|null  $unsupportedCapabilities
     */
    public function __construct(
        public SkillInstallationManagementResultKind $kind,
        public ?CatalogNegotiatedContract $negotiated = null,
        public ?SkillInstallationManagementOutcome $outcome = null,
        public ?string $message = null,
        public ?int $minimumSupportedProtocolVersion = null,
        public CatalogNegotiationRefusedReason|CatalogNetworkFailureReason|null $reason = null,
        public ?int $runtimeProtocolVersion = null,
        public ?array $supportedCapabilities = null,
        public ?array $unsupportedCapabilities = null,
        public ?CatalogInvalidRequestField $field = null,
        public ?int $statusCode = null,
        public ?int $retryAfterSeconds = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: SkillInstallationManagementResultKind::from($data['kind']),
            negotiated: isset($data['negotiated']) ? CatalogNegotiatedContract::fromArray($data['negotiated']) : null,
            outcome: isset($data['outcome']) ? SkillInstallationManagementOutcome::fromArray($data['outcome']) : null,
            message: $data['message'] ?? null,
            minimumSupportedProtocolVersion: $data['minimumSupportedProtocolVersion'] ?? null,
            reason: isset($data['reason']) ? match ($data['kind']) {
                'network-failure' => CatalogNetworkFailureReason::from($data['reason']),
                'negotiation-refused' => CatalogNegotiationRefusedReason::from($data['reason']),
                default => null,
            } : null,
            runtimeProtocolVersion: $data['runtimeProtocolVersion'] ?? null,
            supportedCapabilities: $data['supportedCapabilities'] ?? null,
            unsupportedCapabilities: $data['unsupportedCapabilities'] ?? null,
            field: isset($data['field']) ? CatalogInvalidRequestField::from($data['field']) : null,
            statusCode: isset($data['statusCode']) ? (int) $data['statusCode'] : null,
            retryAfterSeconds: isset($data['retryAfterSeconds']) ? (int) $data['retryAfterSeconds'] : null,
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
            'statusCode' => $this->statusCode,
            'retryAfterSeconds' => $this->retryAfterSeconds,
        ] as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
