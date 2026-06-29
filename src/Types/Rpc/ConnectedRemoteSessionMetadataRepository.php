<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Repository associated with the connected remote session.
 */
readonly class ConnectedRemoteSessionMetadataRepository implements Arrayable
{
    /**
     * @param  string  $branch  Branch associated with the remote session.
     * @param  string  $name  Repository name.
     * @param  string  $owner  Repository owner or organization login.
     */
    public function __construct(
        public string $branch,
        public string $name,
        public string $owner,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            branch: Arr::string($data, 'branch', ''),
            name: Arr::string($data, 'name', ''),
            owner: Arr::string($data, 'owner', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'branch' => $this->branch,
            'name' => $this->name,
            'owner' => $this->owner,
        ];
    }
}
