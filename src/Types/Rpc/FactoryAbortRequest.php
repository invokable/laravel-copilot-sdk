<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for cooperatively aborting a factory body.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryAbortRequest implements Arrayable
{
    /**
     * @param  string  $sessionId  Target session identifier
     * @param  string  $runId  Factory run identifier.
     */
    public function __construct(
        public string $sessionId,
        public string $runId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId'),
            runId: Arr::string($data, 'runId'),
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'runId' => $this->runId,
        ];
    }
}
