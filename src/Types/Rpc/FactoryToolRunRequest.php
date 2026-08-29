<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Internal parameters for invoking a registered factory from a tool.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 * @internal
 */
readonly class FactoryToolRunRequest implements Arrayable
{
    /**
     * @param  mixed  $args  Factory input value.
     * @param  string  $name  Registered factory name.
     * @param  FactoryToolRunOptions|array|null  $options  Internal factory invocation options.
     * @param  ?string  $toolCallId  Opaque identifier of the originating tool call.
     */
    public function __construct(
        public mixed $args,
        public string $name,
        public FactoryToolRunOptions|array|null $options = null,
        public ?string $toolCallId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $options = $data['options'] ?? null;

        return new self(
            args: $data['args'] ?? null,
            name: Arr::string($data, 'name'),
            options: $options !== null
                ? ($options instanceof FactoryToolRunOptions ? $options : FactoryToolRunOptions::fromArray($options))
                : null,
            toolCallId: $data['toolCallId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $options = $this->options instanceof FactoryToolRunOptions ? $this->options->toArray() : $this->options;

        return array_filter([
            'args' => $this->args,
            'name' => $this->name,
            'options' => $options,
            'toolCallId' => $this->toolCallId,
        ], fn ($v) => $v !== null);
    }
}
