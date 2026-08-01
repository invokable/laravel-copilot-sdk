<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for reading a factory journal entry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryJournalGetRequest implements Arrayable
{
    /**
     * @param  string  $executionToken  Capability token authorizing this factory execution.
     * @param  string  $key  Namespaced journal key.
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
