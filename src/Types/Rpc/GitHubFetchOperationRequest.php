<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\GitHubFetchOperation;

/** @experimental */
readonly class GitHubFetchOperationRequest implements Arrayable
{
    /**
     * @param  array<string, string>  $params
     */
    public function __construct(
        public int $requestId,
        public GitHubFetchOperation $operation,
        public array $params,
        public ?string $sessionId = null,
        public ?string $authOwnerId = null,
        public ?string $bodyJson = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::integer($data, 'requestId', 0),
            operation: GitHubFetchOperation::from(Arr::string($data, 'operation', '')),
            params: Arr::array($data, 'params', []),
            sessionId: isset($data['sessionId']) ? Arr::string($data, 'sessionId') : null,
            authOwnerId: isset($data['authOwnerId']) ? Arr::string($data, 'authOwnerId') : null,
            bodyJson: isset($data['bodyJson']) ? Arr::string($data, 'bodyJson') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'sessionId' => $this->sessionId,
            'authOwnerId' => $this->authOwnerId,
            'operation' => $this->operation->value,
            'params' => array_filter($this->params, static fn ($value): bool => $value !== null),
            'bodyJson' => $this->bodyJson,
        ], static fn ($value): bool => $value !== null);
    }
}
