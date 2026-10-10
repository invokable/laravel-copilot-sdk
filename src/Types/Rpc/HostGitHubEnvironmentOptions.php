<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** GitHub Mission Control host registration options. */
readonly class HostGitHubEnvironmentOptions implements Arrayable
{
    public function __construct(
        public string $name,
        public string $computeId,
        public ?bool $requireConnectionBinding = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            computeId: $data['computeId'] ?? '',
            requireConnectionBinding: $data['requireConnectionBinding'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'computeId' => $this->computeId,
            'requireConnectionBinding' => $this->requireConnectionBinding,
        ], fn ($value) => $value !== null);
    }
}
