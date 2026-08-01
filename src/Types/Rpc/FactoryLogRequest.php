<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for recording factory progress.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryLogRequest implements Arrayable
{
    /**
     * @param  string  $executionToken  Capability token authorizing this factory execution.
     * @param  array<FactoryLogLine>  $lines  Ordered progress lines to append.
     * @param  string  $runId  Factory run identifier.
     */
    public function __construct(
        public string $executionToken,
        public array $lines,
        public string $runId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            executionToken: Arr::string($data, 'executionToken'),
            lines: array_map(
                fn (array $line) => FactoryLogLine::fromArray($line),
                $data['lines'] ?? [],
            ),
            runId: Arr::string($data, 'runId'),
        );
    }

    public function toArray(): array
    {
        return [
            'executionToken' => $this->executionToken,
            'lines' => array_map(fn (FactoryLogLine $line) => $line->toArray(), $this->lines),
            'runId' => $this->runId,
        ];
    }
}
