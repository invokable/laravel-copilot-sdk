<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\NetworkScope;

/**
 * Registers the network callbacks for an SDK connection.
 *
 * @experimental
 */
readonly class NetworkSetProviderRequest implements Arrayable
{
    public function __construct(
        public NetworkScope|string|null $scope = null,
        public bool $resolveProxy = false,
        public bool $authenticateNegotiate = false,
    ) {}

    public static function fromArray(array $data): self
    {
        $scope = isset($data['scope']) ? Arr::string($data, 'scope') : null;

        return new self(
            scope: $scope === null ? null : (NetworkScope::tryFrom($scope) ?? $scope),
            resolveProxy: isset($data['resolveProxy']),
            authenticateNegotiate: isset($data['authenticateNegotiate']),
        );
    }

    public function toArray(): array
    {
        $scope = $this->scope instanceof NetworkScope ? $this->scope->value : $this->scope;

        return array_filter([
            'scope' => $scope,
            'resolveProxy' => $this->resolveProxy ? (object) [] : null,
            'authenticateNegotiate' => $this->authenticateNegotiate ? (object) [] : null,
        ], fn ($value) => $value !== null);
    }
}
