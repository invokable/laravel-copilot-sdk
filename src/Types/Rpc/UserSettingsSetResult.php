<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Outcome of writing user settings.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UserSettingsSetResult implements Arrayable
{
    /**
     * @param  string[]  $shadowedKeys  Top-level keys whose write is shadowed by a value still present in the legacy config.json.
     */
    public function __construct(
        public array $shadowedKeys,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            shadowedKeys: Arr::array($data, 'shadowedKeys', []),
        );
    }

    public function toArray(): array
    {
        return [
            'shadowedKeys' => $this->shadowedKeys,
        ];
    }
}
