<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Safe discovery information for the requested environment.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnvironmentsGetResult implements Arrayable
{
    public function __construct(
        public GitHubEnvironment $environment,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(environment: GitHubEnvironment::fromArray($data['environment'] ?? []));
    }

    public function toArray(): array
    {
        return ['environment' => $this->environment->toArray()];
    }
}
