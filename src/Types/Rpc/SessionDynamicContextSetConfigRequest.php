<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to set a session's dynamic-context configuration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionDynamicContextSetConfigRequest implements Arrayable
{
    public function __construct(
        public string $storePath,
        public string $repository,
        public string $branch,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            storePath: $data['storePath'],
            repository: $data['repository'],
            branch: $data['branch'],
        );
    }

    public function toArray(): array
    {
        return [
            'storePath' => $this->storePath,
            'repository' => $this->repository,
            'branch' => $this->branch,
        ];
    }
}
