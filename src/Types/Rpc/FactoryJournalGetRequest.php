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
     * @param  string  $key  Namespaced journal key.
     * @param  string  $runId  Factory run identifier.
     */
    public function __construct(
        public string $key,
        public string $runId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: Arr::string($data, 'key'),
            runId: Arr::string($data, 'runId'),
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'runId' => $this->runId,
        ];
    }
}
