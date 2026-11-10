<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * One persisted feature preference.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FeatureFlagEnabledOverride implements Arrayable
{
    public function __construct(
        public string $name,
        public ?bool $enabled,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            enabled: array_key_exists('enabled', $data) ? $data['enabled'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'enabled' => $this->enabled,
        ];
    }
}
