<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters sent to the owning extension to execute a factory closure.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryExecuteRequest implements Arrayable
{
    /**
     * @param  mixed  $args  Factory input value.
     * @param  string  $name  Registered factory name.
     * @param  string  $runId  Factory run identifier.
     * @param  string  $sessionId  Target session identifier
     */
    public function __construct(
        public mixed $args,
        public string $name,
        public string $runId,
        public string $sessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            args: $data['args'] ?? null,
            name: Arr::string($data, 'name'),
            runId: Arr::string($data, 'runId'),
            sessionId: Arr::string($data, 'sessionId'),
        );
    }

    public function toArray(): array
    {
        return [
            'args' => $this->args,
            'name' => $this->name,
            'runId' => $this->runId,
            'sessionId' => $this->sessionId,
        ];
    }
}
