<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class FetchResolveProxyResult implements Arrayable
{
    /**
     * @param  FetchProxyRoute[]  $routes
     */
    public function __construct(
        public array $routes,
        public string $source,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            routes: array_map(
                static fn (array $route): FetchProxyRoute => FetchProxyRoute::fromArray($route),
                Arr::array($data, 'routes', []),
            ),
            source: Arr::string($data, 'source', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'routes' => array_map(
                static fn (FetchProxyRoute $route): array => $route->toArray(),
                $this->routes,
            ),
            'source' => $this->source,
        ];
    }
}
