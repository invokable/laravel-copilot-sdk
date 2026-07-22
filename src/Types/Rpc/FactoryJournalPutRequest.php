<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for storing a factory journal entry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryJournalPutRequest implements Arrayable
{
    /**
     * @param  string  $key  Namespaced journal key.
     * @param  mixed  $resultJson  JSON result to memoize.
     * @param  string  $runId  Factory run identifier.
     */
    public function __construct(
        public string $key,
        public mixed $resultJson,
        public string $runId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: Arr::string($data, 'key'),
            resultJson: $data['resultJson'] ?? null,
            runId: Arr::string($data, 'runId'),
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'resultJson' => $this->resultJson,
            'runId' => $this->runId,
        ];
    }
}
