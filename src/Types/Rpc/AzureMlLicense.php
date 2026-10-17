<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * License published with an eligible Azure ML model.
 *
 * @experimental
 */
readonly class AzureMlLicense implements Arrayable
{
    public function __construct(
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(name: $data['name']);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
