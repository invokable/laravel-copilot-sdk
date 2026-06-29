<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Per-key metadata for every known user setting.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UserSettingsGetResult implements Arrayable
{
    /**
     * @param  array<string, UserSettingMetadata>  $settings  Every known user setting keyed by setting name.
     */
    public function __construct(
        public array $settings,
    ) {}

    public static function fromArray(array $data): self
    {
        $settings = [];
        foreach (Arr::array($data, 'settings', []) as $key => $value) {
            $settings[$key] = UserSettingMetadata::fromArray((array) $value);
        }

        return new self(settings: $settings);
    }

    public function toArray(): array
    {
        $settings = [];
        foreach ($this->settings as $key => $metadata) {
            $settings[$key] = $metadata->toArray();
        }

        return ['settings' => $settings];
    }
}
