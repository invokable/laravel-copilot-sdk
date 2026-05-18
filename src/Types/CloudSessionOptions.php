<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Options for creating a remote session in the cloud.
 */
readonly class CloudSessionOptions implements Arrayable
{
    /**
     * @param  CloudSessionRepository|array|null  $repository  GitHub repository metadata to associate with the cloud session
     */
    public function __construct(
        public CloudSessionRepository|array|null $repository = null,
    ) {}

    /**
     * Create from array data.
     */
    public static function fromArray(array $data): static
    {
        $repository = null;
        if (isset($data['repository'])) {
            $repository = $data['repository'] instanceof CloudSessionRepository
                ? $data['repository']
                : CloudSessionRepository::fromArray($data['repository']);
        }

        return new static(
            repository: $repository,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $repository = $this->repository instanceof CloudSessionRepository
            ? $this->repository->toArray()
            : $this->repository;

        return array_filter([
            'repository' => $repository,
        ], fn ($value) => $value !== null);
    }
}
