<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to search the MCP registry.
 *
 * @experimental
 *
 * @internal Internal SDK API; not part of the public surface.
 */
readonly class McpRegistrySearchRequest implements Arrayable
{
    /**
     * @param  mixed  $authInfo  Opaque JSON authentication identity used for the registry search
     */
    public function __construct(
        public int $requestId,
        public mixed $authInfo,
        public int $limit,
        public ?string $query = null,
        public ?string $repository = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::integer($data, 'requestId'),
            authInfo: $data['authInfo'] ?? null,
            limit: Arr::integer($data, 'limit'),
            query: isset($data['query']) ? Arr::string($data, 'query') : null,
            repository: isset($data['repository']) ? Arr::string($data, 'repository') : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'requestId' => $this->requestId,
            'authInfo' => $this->authInfo,
        ];

        if ($this->query !== null) {
            $result['query'] = $this->query;
        }

        if ($this->repository !== null) {
            $result['repository'] = $this->repository;
        }

        $result['limit'] = $this->limit;

        return $result;
    }
}
