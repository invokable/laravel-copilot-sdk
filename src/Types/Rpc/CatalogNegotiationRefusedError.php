<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogCapability;
use Revolution\Copilot\Enums\CatalogNegotiationRefusedReason;

/**
 * The caller's protocol version or required capabilities cannot be honoured.
 *
 * @experimental
 */
readonly class CatalogNegotiationRefusedError implements Arrayable
{
    public string $kind;

    /**
     * @param  CatalogCapability[]  $supportedCapabilities
     * @param  string[]  $unsupportedCapabilities
     */
    public function __construct(
        public CatalogNegotiationRefusedReason $reason,
        public int $runtimeProtocolVersion,
        public int $minimumSupportedProtocolVersion,
        public array $supportedCapabilities,
        public array $unsupportedCapabilities,
        public string $message,
    ) {
        $this->kind = 'negotiation-refused';
    }

    public static function fromArray(array $data): static
    {
        return new static(
            reason: CatalogNegotiationRefusedReason::from($data['reason']),
            runtimeProtocolVersion: (int) ($data['runtimeProtocolVersion'] ?? 0),
            minimumSupportedProtocolVersion: (int) ($data['minimumSupportedProtocolVersion'] ?? 0),
            supportedCapabilities: array_map(
                fn (string $c) => CatalogCapability::from($c),
                $data['supportedCapabilities'] ?? [],
            ),
            unsupportedCapabilities: $data['unsupportedCapabilities'] ?? [],
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'reason' => $this->reason->value,
            'runtimeProtocolVersion' => $this->runtimeProtocolVersion,
            'minimumSupportedProtocolVersion' => $this->minimumSupportedProtocolVersion,
            'supportedCapabilities' => array_map(fn ($c) => $c->value, $this->supportedCapabilities),
            'unsupportedCapabilities' => $this->unsupportedCapabilities,
            'message' => $this->message,
        ];
    }
}
