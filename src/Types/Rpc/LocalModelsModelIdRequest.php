<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Identifier of a job or notice owned by this connection.
 *
 * @experimental
 */
readonly class LocalModelsModelIdRequest implements Arrayable
{
    public function __construct(
        public string $modelId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            modelId: $data['modelId'] ?? '',
        );
    }

    public function toArray(): array
    {
        return ['modelId' => $this->modelId];
    }
}
