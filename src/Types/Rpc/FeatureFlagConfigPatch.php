<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Patch to retained feature-flag settings.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FeatureFlagConfigPatch implements Arrayable
{
    /**
     * @param  string[]  $undefinedKeys
     */
    public function __construct(
        public mixed $values,
        public array $undefinedKeys = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            values: $data['values'] ?? null,
            undefinedKeys: Arr::array($data, 'undefinedKeys', []),
        );
    }

    public function toArray(): array
    {
        return [
            'values' => $this->values,
            'undefinedKeys' => $this->undefinedKeys,
        ];
    }
}
