<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FactoryLogLineKind;

/**
 * A single factory progress record.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryProgressLine implements Arrayable
{
    /**
     * @param  int  $seq  Monotonic sequence number within the run.
     * @param  int  $attempt  Run attempt the record belongs to.
     * @param  ?string  $phaseId  Phase the record belongs to, or null.
     * @param  int  $recordedAt  Epoch milliseconds when the record was captured.
     * @param  FactoryLogLineKind|string  $kind  Kind of progress record.
     * @param  string  $text  Record text.
     */
    public function __construct(
        public int $seq,
        public int $attempt,
        public ?string $phaseId,
        public int $recordedAt,
        public FactoryLogLineKind|string $kind,
        public string $text,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            seq: Arr::integer($data, 'seq', 0),
            attempt: Arr::integer($data, 'attempt', 0),
            phaseId: $data['phaseId'] ?? null,
            recordedAt: Arr::integer($data, 'recordedAt', 0),
            kind: $data['kind'] instanceof FactoryLogLineKind ? $data['kind'] : FactoryLogLineKind::from($data['kind']),
            text: Arr::string($data, 'text', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'seq' => $this->seq,
            'attempt' => $this->attempt,
            'phaseId' => $this->phaseId,
            'recordedAt' => $this->recordedAt,
            'kind' => $this->kind instanceof FactoryLogLineKind ? $this->kind->value : $this->kind,
            'text' => $this->text,
        ];
    }
}
