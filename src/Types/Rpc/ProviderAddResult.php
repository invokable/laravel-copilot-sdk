<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The selectable model entries synthesized for the models added by this call.
 *
 * @experimental This type is part of an experimental multi-provider BYOK surface
 * and may change or be removed in future SDK or CLI releases.
 */
readonly class ProviderAddResult implements Arrayable
{
    /**
     * @param  array<mixed>  $models  Synthesized selectable model entries for the newly added BYOK models.
     *                                Empty when only providers were added.
     */
    public function __construct(
        public array $models,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            models: Arr::array($data, 'models', []),
        );
    }

    public function toArray(): array
    {
        return [
            'models' => $this->models,
        ];
    }
}
