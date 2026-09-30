<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Environments visible to the authenticated caller and matching the supplied filters.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnvironmentsListResult implements Arrayable
{
    /**
     * @param  list<GitHubEnvironment>  $environments
     */
    public function __construct(
        public array $environments = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            environments: array_map(
                fn (array $e) => GitHubEnvironment::fromArray($e),
                array_values($data['environments'] ?? []),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'environments' => array_map(fn (GitHubEnvironment $e) => $e->toArray(), $this->environments),
        ];
    }
}
