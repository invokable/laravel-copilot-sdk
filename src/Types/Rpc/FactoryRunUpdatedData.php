<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Payload of the `factory.run_updated` session event.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunUpdatedData implements Arrayable
{
    /**
     * @param  int  $revision  Monotonic run observation revision.
     * @param  string  $runId  Factory run identifier that changed.
     */
    public function __construct(
        public int $revision,
        public string $runId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            revision: Arr::integer($data, 'revision', 0),
            runId: Arr::string($data, 'runId', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'revision' => $this->revision,
            'runId' => $this->runId,
        ];
    }
}
