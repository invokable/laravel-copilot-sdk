<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubReleaseQuery;

/** @experimental */
readonly class GitHubReleaseLookupRequest implements Arrayable
{
    public function __construct(
        public int $requestId,
        public GitHubReleaseQuery $query,
        public mixed $authInfo = null,
        public ?string $value = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::integer($data, 'requestId', 0),
            query: GitHubReleaseQuery::from(Arr::string($data, 'query', '')),
            authInfo: $data['authInfo'] ?? null,
            value: isset($data['value']) ? Arr::string($data, 'value') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'query' => $this->query->value,
            'authInfo' => $this->authInfo,
            'value' => $this->value,
        ], static fn ($field): bool => $field !== null);
    }
}
