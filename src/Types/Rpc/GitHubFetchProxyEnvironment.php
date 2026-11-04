<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubFetchProxyEnvironment implements Arrayable
{
    public function __construct(
        public ?string $httpProxy = null,
        public ?string $httpsProxy = null,
        public ?string $noProxy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            httpProxy: isset($data['httpProxy']) ? Arr::string($data, 'httpProxy') : null,
            httpsProxy: isset($data['httpsProxy']) ? Arr::string($data, 'httpsProxy') : null,
            noProxy: isset($data['noProxy']) ? Arr::string($data, 'noProxy') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'httpProxy' => $this->httpProxy,
            'httpsProxy' => $this->httpsProxy,
            'noProxy' => $this->noProxy,
        ], static fn ($value): bool => $value !== null);
    }
}
