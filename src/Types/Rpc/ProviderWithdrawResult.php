<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * What the withdrawal actually removed from the registry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ProviderWithdrawResult implements Arrayable
{
    /**
     * @param  list<string>  $providersRemoved  Providers removed because a withdrawn model was the last entry referencing them.
     * @param  list<string>  $withdrawn  Models actually withdrawn.
     * @param  bool|null  $modelDeselected  Whether the session's explicit model selection was cleared.
     */
    public function __construct(
        public array $providersRemoved = [],
        public array $withdrawn = [],
        public ?bool $modelDeselected = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            providersRemoved: array_values(Arr::array($data, 'providersRemoved', [])),
            withdrawn: array_values(Arr::array($data, 'withdrawn', [])),
            modelDeselected: isset($data['modelDeselected']) ? (bool) $data['modelDeselected'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'providersRemoved' => $this->providersRemoved,
            'withdrawn' => $this->withdrawn,
            'modelDeselected' => $this->modelDeselected,
        ], fn ($v) => $v !== null);
    }
}
