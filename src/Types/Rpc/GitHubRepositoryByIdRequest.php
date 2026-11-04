<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class GitHubRepositoryByIdRequest implements Arrayable
{
    public function __construct(
        public mixed $authInfo,
        public int $repositoryId,
        public ?string $authContextId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            authInfo: $data['authInfo'] ?? null,
            repositoryId: Arr::integer($data, 'repositoryId', 0),
            authContextId: isset($data['authContextId']) ? Arr::string($data, 'authContextId') : null,
        );
    }

    public function toArray(): array
    {
        $data = ['authInfo' => $this->authInfo];

        if ($this->authContextId !== null) {
            $data['authContextId'] = $this->authContextId;
        }

        $data['repositoryId'] = $this->repositoryId;

        return $data;
    }
}
