<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for retrieving a factory run.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryGetRunRequest implements Arrayable
{
    /**
     * @param  string  $runId  Factory run identifier.
     */
    public function __construct(
        public string $runId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            runId: Arr::string($data, 'runId'),
        );
    }

    public function toArray(): array
    {
        return [
            'runId' => $this->runId,
        ];
    }
}
