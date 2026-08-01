<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for reading a page of factory run progress records.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryGetRunProgressRequest implements Arrayable
{
    /**
     * @param  string  $runId  Factory run identifier.
     * @param  ?string  $phaseId  Restrict records to this phase.
     * @param  ?int  $afterSeq  Return records after this sequence number.
     * @param  ?int  $beforeSeq  Return records before this sequence number.
     * @param  ?int  $limit  Maximum number of records to return.
     */
    public function __construct(
        public string $runId,
        public ?string $phaseId = null,
        public ?int $afterSeq = null,
        public ?int $beforeSeq = null,
        public ?int $limit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            runId: Arr::string($data, 'runId'),
            phaseId: $data['phaseId'] ?? null,
            afterSeq: $data['afterSeq'] ?? null,
            beforeSeq: $data['beforeSeq'] ?? null,
            limit: $data['limit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'runId' => $this->runId,
            'phaseId' => $this->phaseId,
            'afterSeq' => $this->afterSeq,
            'beforeSeq' => $this->beforeSeq,
            'limit' => $this->limit,
        ], fn ($v) => $v !== null);
    }
}
