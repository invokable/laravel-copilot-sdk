<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * The list of models available to a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionModelList implements Arrayable
{
    /**
     * @param  array  $list  Available models, ordered with the most preferred default first.
     * @param  ?array  $quotaSnapshots  Per-quota snapshots returned alongside the model list, keyed by quota type.
     */
    public function __construct(
        public array $list = [],
        public ?array $quotaSnapshots = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            list: $data['list'] ?? [],
            quotaSnapshots: $data['quotaSnapshots'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'list' => $this->list,
            'quotaSnapshots' => $this->quotaSnapshots,
        ], fn ($v) => $v !== null);
    }
}
