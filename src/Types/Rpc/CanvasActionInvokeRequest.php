<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Canvas action invocation parameters.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CanvasActionInvokeRequest implements Arrayable
{
    /**
     * @param  string  $instanceId  Open canvas instance identifier.
     * @param  string  $actionName  Action name to invoke.
     * @param  mixed  $input  Action input.
     */
    public function __construct(
        public string $instanceId,
        public string $actionName,
        public mixed $input = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            instanceId: $data['instanceId'],
            actionName: $data['actionName'],
            input: $data['input'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'instanceId' => $this->instanceId,
            'actionName' => $this->actionName,
            'input' => $this->input,
        ], fn ($value) => $value !== null);
    }
}
