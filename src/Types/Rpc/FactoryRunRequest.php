<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for invoking a registered factory.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunRequest implements Arrayable
{
    /**
     * @param  mixed  $args  Factory input value.
     * @param  string  $name  Registered factory name.
     * @param  RunOptions|array|null  $options  Factory invocation options.
     */
    public function __construct(
        public mixed $args,
        public string $name,
        public RunOptions|array|null $options = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $options = $data['options'] ?? null;

        return new self(
            args: $data['args'] ?? null,
            name: Arr::string($data, 'name'),
            options: $options !== null
                ? ($options instanceof RunOptions ? $options : RunOptions::fromArray($options))
                : null,
        );
    }

    public function toArray(): array
    {
        $options = $this->options instanceof RunOptions ? $this->options->toArray() : $this->options;

        return array_filter([
            'args' => $this->args,
            'name' => $this->name,
            'options' => $options,
        ], fn ($v) => $v !== null);
    }
}
