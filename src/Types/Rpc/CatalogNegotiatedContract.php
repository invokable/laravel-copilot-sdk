<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogCapability;

/**
 * The protocol version and capability set the runtime actually honoured.
 *
 * @experimental
 */
readonly class CatalogNegotiatedContract implements Arrayable
{
    /**
     * @param  int  $runtimeProtocolVersion  Protocol version of the runtime that served the request.
     * @param  CatalogCapability[]  $grantedCapabilities  Wire features the runtime understood for this operation.
     */
    public function __construct(
        public int $runtimeProtocolVersion,
        public array $grantedCapabilities,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            runtimeProtocolVersion: (int) ($data['runtimeProtocolVersion'] ?? 0),
            grantedCapabilities: array_map(
                fn (string $c) => CatalogCapability::from($c),
                $data['grantedCapabilities'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'runtimeProtocolVersion' => $this->runtimeProtocolVersion,
            'grantedCapabilities' => array_map(fn ($c) => $c->value, $this->grantedCapabilities),
        ];
    }
}
