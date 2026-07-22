<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\FactoryLogLineKind;

/**
 * One ordered factory progress line.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryLogLine implements Arrayable
{
    /**
     * @param  FactoryLogLineKind|string  $kind  Progress line kind.
     * @param  int  $seq  Monotonic sequence number within the factory run.
     * @param  string  $text  Progress text.
     */
    public function __construct(
        public FactoryLogLineKind|string $kind,
        public int $seq,
        public string $text,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: $data['kind'] instanceof FactoryLogLineKind ? $data['kind'] : FactoryLogLineKind::from($data['kind']),
            seq: Arr::integer($data, 'seq'),
            text: Arr::string($data, 'text'),
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind instanceof FactoryLogLineKind ? $this->kind->value : $this->kind,
            'seq' => $this->seq,
            'text' => $this->text,
        ];
    }
}
