<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Service-published warning text shown when presenting a model.
 */
readonly class ModelWarningText implements Arrayable
{
    public function __construct(public ?string $dataRetention = null) {}

    public static function fromArray(array $data): self
    {
        return new self(dataRetention: $data['dataRetention'] ?? null);
    }

    public function toArray(): array
    {
        return array_filter(['dataRetention' => $this->dataRetention], fn ($value) => $value !== null);
    }
}
