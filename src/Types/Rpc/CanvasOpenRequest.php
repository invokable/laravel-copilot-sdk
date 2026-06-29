<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Canvas open parameters.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class CanvasOpenRequest implements Arrayable
{
    /**
     * @param  string  $canvasId  Provider-local canvas identifier.
     * @param  string  $instanceId  Caller-supplied stable instance identifier.
     * @param  string|null  $extensionId  Owning provider identifier. Optional when canvasId is unique across providers; required to disambiguate when multiple providers register the same canvasId.
     * @param  mixed  $input  Canvas open input.
     */
    public function __construct(
        public string $canvasId,
        public string $instanceId,
        public ?string $extensionId = null,
        public mixed $input = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            canvasId: Arr::string($data, 'canvasId'),
            instanceId: Arr::string($data, 'instanceId'),
            extensionId: $data['extensionId'] ?? null,
            input: $data['input'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'canvasId' => $this->canvasId,
            'instanceId' => $this->instanceId,
            'extensionId' => $this->extensionId,
            'input' => $this->input,
        ], fn ($value) => $value !== null);
    }
}
