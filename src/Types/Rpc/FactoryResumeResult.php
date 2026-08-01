<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of resuming a halted factory run.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryResumeResult implements Arrayable
{
    /**
     * @param  string  $factoryName  Registered factory name.
     * @param  FactoryRunResult|array  $run  Current or terminal factory run envelope.
     */
    public function __construct(
        public string $factoryName,
        public FactoryRunResult|array $run,
    ) {}

    public static function fromArray(array $data): self
    {
        $run = $data['run'] ?? [];

        return new self(
            factoryName: Arr::string($data, 'factoryName'),
            run: $run instanceof FactoryRunResult ? $run : FactoryRunResult::fromArray($run),
        );
    }

    public function toArray(): array
    {
        return [
            'factoryName' => $this->factoryName,
            'run' => $this->run instanceof FactoryRunResult ? $this->run->toArray() : $this->run,
        ];
    }
}
