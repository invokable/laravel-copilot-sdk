<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Registers the Kerberos credential provider for an SDK connection.
 *
 * @experimental
 */
readonly class KerberosAuthSetProviderRequest implements Arrayable
{
    public function __construct(
        public string $mode,
        public ?string $scope = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: Arr::string($data, 'mode'),
            scope: isset($data['scope']) ? Arr::string($data, 'scope') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode,
            'scope' => $this->scope,
        ], fn ($value) => $value !== null);
    }
}
