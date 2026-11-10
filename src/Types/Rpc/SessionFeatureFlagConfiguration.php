<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SessionFeatureFlagsMode;

/**
 * Session-lifecycle feature-flag configuration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 *
 * @internal
 */
readonly class SessionFeatureFlagConfiguration implements Arrayable
{
    public function __construct(
        public mixed $initOptions,
        public SessionFeatureFlagsMode $mode,
        public ?bool $streamerMode = null,
        public ?int $firstLaunchAtMillis = null,
        public ?bool $settingsAreLive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            initOptions: $data['initOptions'] ?? null,
            mode: $data['mode'] instanceof SessionFeatureFlagsMode
                ? $data['mode']
                : SessionFeatureFlagsMode::from(Arr::string($data, 'mode')),
            streamerMode: isset($data['streamerMode']) ? Arr::boolean($data, 'streamerMode') : null,
            firstLaunchAtMillis: isset($data['firstLaunchAtMillis']) ? Arr::integer($data, 'firstLaunchAtMillis') : null,
            settingsAreLive: isset($data['settingsAreLive']) ? Arr::boolean($data, 'settingsAreLive') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'initOptions' => $this->initOptions,
            'mode' => $this->mode->value,
            'streamerMode' => $this->streamerMode,
            'firstLaunchAtMillis' => $this->firstLaunchAtMillis,
            'settingsAreLive' => $this->settingsAreLive,
        ], static fn ($value, $key): bool => $value !== null || $key === 'initOptions', ARRAY_FILTER_USE_BOTH);
    }
}
