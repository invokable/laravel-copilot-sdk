<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class FetchProxyRoute implements Arrayable
{
    public function __construct(public ?string $proxyUrl = null) {}

    public static function fromArray(array $data): self
    {
        return new self(
            proxyUrl: isset($data['proxyUrl']) ? Arr::string($data, 'proxyUrl') : null,
        );
    }

    public function toArray(): array
    {
        return $this->proxyUrl === null ? [] : ['proxyUrl' => $this->proxyUrl];
    }
}
