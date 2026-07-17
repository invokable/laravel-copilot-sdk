<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The list of models available to a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionModelList implements Arrayable
{
    /**
     * @param  array  $list  Available models, ordered with the most preferred default first.
     * @param  ?array<SessionModelPriceCategory>  $modelPriceCategories  Cost categories for the full CAPI catalog, including picker-disabled models that Auto may select.
     * @param  ?array  $quotaSnapshots  Per-quota snapshots returned alongside the model list, keyed by quota type.
     */
    public function __construct(
        public array $list = [],
        public ?array $modelPriceCategories = null,
        public ?array $quotaSnapshots = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            list: Arr::array($data, 'list', []),
            modelPriceCategories: isset($data['modelPriceCategories'])
                ? array_map(fn (array $c) => SessionModelPriceCategory::fromArray($c), $data['modelPriceCategories'])
                : null,
            quotaSnapshots: $data['quotaSnapshots'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'list' => $this->list,
            'modelPriceCategories' => $this->modelPriceCategories !== null
                ? array_map(fn (SessionModelPriceCategory $c) => $c->toArray(), $this->modelPriceCategories)
                : null,
            'quotaSnapshots' => $this->quotaSnapshots,
        ], fn ($v) => $v !== null);
    }
}
