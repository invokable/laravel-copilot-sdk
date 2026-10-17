<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The GitHub repository and commit an installed plugin is pinned to.
 *
 * @experimental
 */
readonly class InstalledPluginVerifiedSource implements Arrayable
{
    public function __construct(
        public string $repository,
        public string $revision,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            repository: $data['repository'],
            revision: $data['revision'],
        );
    }

    public function toArray(): array
    {
        return [
            'repository' => $this->repository,
            'revision' => $this->revision,
        ];
    }
}
