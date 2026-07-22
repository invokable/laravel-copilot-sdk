<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FactoryRunStatus;

/**
 * Complete current or terminal factory run envelope.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryRunResult implements Arrayable
{
    /**
     * @param  string  $runId  Factory run identifier.
     * @param  FactoryRunStatus|string  $status  Current or terminal factory run status.
     * @param  ?string  $error  Error message for an errored run.
     * @param  FactoryRunFailure|array|null  $failure  Machine-readable failure details for an errored run.
     * @param  ?string  $reason  Reason for a halted or cancelled run.
     * @param  mixed  $result  Completed factory result.
     * @param  mixed  $snapshot  Partial journal and progress snapshot for a halted, cancelled, or errored run.
     */
    public function __construct(
        public string $runId,
        public FactoryRunStatus|string $status,
        public ?string $error = null,
        public FactoryRunFailure|array|null $failure = null,
        public ?string $reason = null,
        public mixed $result = null,
        public mixed $snapshot = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $failure = $data['failure'] ?? null;

        return new self(
            runId: Arr::string($data, 'runId'),
            status: $data['status'] instanceof FactoryRunStatus ? $data['status'] : FactoryRunStatus::from($data['status']),
            error: $data['error'] ?? null,
            failure: $failure !== null
                ? ($failure instanceof FactoryRunFailure ? $failure : FactoryRunFailure::fromArray($failure))
                : null,
            reason: $data['reason'] ?? null,
            result: $data['result'] ?? null,
            snapshot: $data['snapshot'] ?? null,
        );
    }

    public function toArray(): array
    {
        $failure = $this->failure instanceof FactoryRunFailure ? $this->failure->toArray() : $this->failure;

        return array_filter([
            'runId' => $this->runId,
            'status' => $this->status instanceof FactoryRunStatus ? $this->status->value : $this->status,
            'error' => $this->error,
            'failure' => $failure,
            'reason' => $this->reason,
            'result' => $this->result,
            'snapshot' => $this->snapshot,
        ], fn ($v) => $v !== null);
    }
}
