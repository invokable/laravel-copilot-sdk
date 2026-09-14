<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for an owned durable pause checkpoint.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryPauseCheckpointRequest implements Arrayable
{
    /**
     * @param  string  $executionToken  Opaque token identifying the execution attempt that reached the checkpoint.
     * @param  string  $key  Stable author-defined checkpoint key.
     * @param  string  $runId  Factory run identifier.
     */
    public function __construct(
        public string $executionToken,
        public string $key,
        public string $runId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            executionToken: Arr::string($data, 'executionToken'),
            key: Arr::string($data, 'key'),
            runId: Arr::string($data, 'runId'),
        );
    }

    public function toArray(): array
    {
        return [
            'executionToken' => $this->executionToken,
            'key' => $this->key,
            'runId' => $this->runId,
        ];
    }
}
