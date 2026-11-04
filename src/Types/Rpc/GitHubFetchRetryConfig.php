<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class GitHubFetchRetryConfig implements Arrayable
{
    public function __construct(
        public ?int $maxRetries = null,
        public ?float $defaultRetryDelaySeconds = null,
        public ?float $backoffFactor = null,
        public ?bool $enableCircuitBreaker = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            maxRetries: isset($data['maxRetries']) ? Arr::integer($data, 'maxRetries') : null,
            defaultRetryDelaySeconds: isset($data['defaultRetryDelaySeconds']) ? Arr::float($data, 'defaultRetryDelaySeconds') : null,
            backoffFactor: isset($data['backoffFactor']) ? Arr::float($data, 'backoffFactor') : null,
            enableCircuitBreaker: isset($data['enableCircuitBreaker']) ? Arr::boolean($data, 'enableCircuitBreaker') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'maxRetries' => $this->maxRetries,
            'defaultRetryDelaySeconds' => $this->defaultRetryDelaySeconds,
            'backoffFactor' => $this->backoffFactor,
            'enableCircuitBreaker' => $this->enableCircuitBreaker,
        ], static fn ($value): bool => $value !== null);
    }
}
