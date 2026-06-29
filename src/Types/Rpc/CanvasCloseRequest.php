<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Canvas close parameters.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class CanvasCloseRequest implements Arrayable
{
    /**
     * @param  string  $instanceId  Open canvas instance identifier.
     */
    public function __construct(
        public string $instanceId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            instanceId: Arr::string($data, 'instanceId'),
        );
    }

    public function toArray(): array
    {
        return [
            'instanceId' => $this->instanceId,
        ];
    }
}
