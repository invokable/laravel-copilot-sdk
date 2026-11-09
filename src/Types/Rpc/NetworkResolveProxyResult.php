<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Proxy list returned for a request target.
 *
 * @experimental
 */
readonly class NetworkResolveProxyResult implements Arrayable
{
    public function __construct(
        public ?string $proxy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            proxy: isset($data['proxy']) ? Arr::string($data, 'proxy') : null,
        );
    }

    public function toArray(): array
    {
        return $this->proxy === null ? [] : ['proxy' => $this->proxy];
    }
}
