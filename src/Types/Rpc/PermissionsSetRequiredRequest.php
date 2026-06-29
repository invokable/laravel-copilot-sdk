<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to toggle permission request event bridging.
 */
readonly class PermissionsSetRequiredRequest implements Arrayable
{
    public function __construct(
        public bool $required,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            required: Arr::boolean($data, 'required'),
        );
    }

    public function toArray(): array
    {
        return [
            'required' => $this->required,
        ];
    }
}
