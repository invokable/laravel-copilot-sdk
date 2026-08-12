<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for paging factory runs.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FactoryListRunsRequest implements Arrayable
{
    public function __construct(
        public ?int $afterSeq = null,
        public ?int $beforeSeq = null,
        public ?int $limit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            afterSeq: isset($data['afterSeq']) ? Arr::integer($data, 'afterSeq') : null,
            beforeSeq: isset($data['beforeSeq']) ? Arr::integer($data, 'beforeSeq') : null,
            limit: isset($data['limit']) ? Arr::integer($data, 'limit') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'afterSeq' => $this->afterSeq,
            'beforeSeq' => $this->beforeSeq,
            'limit' => $this->limit,
        ], fn ($value) => $value !== null);
    }
}
