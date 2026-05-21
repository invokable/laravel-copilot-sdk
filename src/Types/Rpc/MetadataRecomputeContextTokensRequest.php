<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for re-tokenizing session context against a model.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataRecomputeContextTokensRequest implements Arrayable
{
    public function __construct(
        public string $modelId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'modelId' => $this->modelId,
        ];
    }
}
