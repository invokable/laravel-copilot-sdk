<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Partial user settings to write to settings.json.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UserSettingsSetRequest implements Arrayable
{
    /**
     * @param  array  $settings  Partial user settings to write, keyed by setting name.
     */
    public function __construct(
        public array $settings,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            settings: Arr::array($data, 'settings', []),
        );
    }

    public function toArray(): array
    {
        return [
            'settings' => $this->settings,
        ];
    }
}
