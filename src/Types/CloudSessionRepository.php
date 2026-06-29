<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * GitHub repository metadata to associate with a cloud session.
 */
readonly class CloudSessionRepository implements Arrayable
{
    /**
     * @param  string  $owner  Repository owner (user or organization name)
     * @param  string  $name  Repository name
     * @param  ?string  $branch  Optional branch name
     */
    public function __construct(
        public string $owner,
        public string $name,
        public ?string $branch = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        return new static(
            owner: Arr::string($data, 'owner', ''),
            name: Arr::string($data, 'name', ''),
            branch: $data['branch'] ?? null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'owner' => $this->owner,
            'name' => $this->name,
            'branch' => $this->branch,
        ], fn ($value) => $value !== null);
    }
}
