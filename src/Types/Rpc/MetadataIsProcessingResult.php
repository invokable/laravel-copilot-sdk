<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Indicates whether a session is currently processing.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataIsProcessingResult implements Arrayable
{
    public function __construct(
        public bool $processing,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            processing: Arr::boolean($data, 'processing', false),
        );
    }

    public function toArray(): array
    {
        return [
            'processing' => $this->processing,
        ];
    }
}
