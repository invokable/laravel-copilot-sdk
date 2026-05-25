<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Canvas action invocation parameters.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class CanvasInvokeActionRequest implements Arrayable
{
    /**
     * @param  string  $actionName  Action name to invoke.
     * @param  string  $instanceId  Open canvas instance identifier.
     * @param  mixed  $input  Action input.
     */
    public function __construct(
        public string $actionName,
        public string $instanceId,
        public mixed $input = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            actionName: $data['actionName'],
            instanceId: $data['instanceId'],
            input: $data['input'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'actionName' => $this->actionName,
            'instanceId' => $this->instanceId,
            'input' => $this->input,
        ], fn ($value) => $value !== null);
    }
}
