<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Optional workspace metadata to update.
 *
 * @experimental
 */
readonly class WorkspacesUpdateMetadataRequest implements Arrayable
{
    public function __construct(
        public mixed $context = null,
        public ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            context: $data['context'] ?? null,
            name: $data['name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'context' => $this->context,
            'name' => $this->name,
        ], static fn ($value) => $value !== null);
    }
}
