<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Cached model selection policy for a session.
 *
 * @experimental
 */
readonly class SessionModelGetSelectionPolicyResult implements Arrayable
{
    /**
     * @param  array<int, ModelSelectionPolicyEntry>  $models
     */
    public function __construct(
        public bool $ready = false,
        public array $models = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ready: $data['ready'] ?? false,
            models: array_map(
                fn (array $model) => ModelSelectionPolicyEntry::fromArray($model),
                $data['models'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'ready' => $this->ready,
            'models' => array_map(fn (ModelSelectionPolicyEntry $model) => $model->toArray(), $this->models),
        ];
    }
}
