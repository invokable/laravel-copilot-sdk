<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderDiscoverResult implements Arrayable
{
    /** @param ModelProviderInstance[] $instances */
    public function __construct(
        public array $instances = [],
        public ?ModelProviderOperationOutcome $outcome = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            instances: array_map(
                fn (array $instance) => ModelProviderInstance::fromArray($instance),
                $data['instances'] ?? [],
            ),
            outcome: isset($data['outcome'])
                ? ModelProviderOperationOutcome::fromArray($data['outcome'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'instances' => array_map(
                fn (ModelProviderInstance $instance) => $instance->toArray(),
                $this->instances,
            ),
            'outcome' => $this->outcome?->toArray(),
        ], static fn ($value) => $value !== null);
    }
}
