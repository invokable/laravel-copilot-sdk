<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Inbound request to resolve the proxy for a request target.
 *
 * @experimental
 */
readonly class NetworkResolveProxyRequest implements Arrayable
{
    public function __construct(
        public string $url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: Arr::string($data, 'url'),
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
        ];
    }
}
