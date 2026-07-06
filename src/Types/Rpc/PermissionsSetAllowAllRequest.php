<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionsAllowAllMode;
use Revolution\Copilot\Enums\PermissionsSetApproveAllSource;

/**
 * Request to set the allow-all mode for the session.
 *
 * @experimental
 */
readonly class PermissionsSetAllowAllRequest implements Arrayable
{
    /**
     * @param  bool|null  $enabled  Legacy full allow-all toggle. Prefer `mode`; when `mode` is omitted,
     *                              `enabled: true` is treated as `mode: "on"` and any other value is treated as `mode: "off"`.
     * @param  PermissionsAllowAllMode|null  $mode  Allow-all mode to apply. `on` enables full allow-all;
     *                                              `auto` enables advisory LLM auto-approval; `off` disables both.
     * @param  string|null  $model  Optional model id for the `auto` mode auto-approval LLM judging.
     *                              Only meaningful when `mode` is `auto`; ignored otherwise.
     * @param  PermissionsSetApproveAllSource|string|null  $source  Optional source for allow-all telemetry.
     */
    public function __construct(
        public ?bool $enabled = null,
        public ?PermissionsAllowAllMode $mode = null,
        public ?string $model = null,
        public PermissionsSetApproveAllSource|string|null $source = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            enabled: isset($data['enabled']) ? Arr::boolean($data, 'enabled') : null,
            mode: isset($data['mode']) ? PermissionsAllowAllMode::tryFrom($data['mode']) : null,
            model: Arr::get($data, 'model'),
            source: isset($data['source']) ? PermissionsSetApproveAllSource::tryFrom($data['source']) ?? $data['source'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'enabled' => $this->enabled,
            'mode' => $this->mode?->value,
            'model' => $this->model,
            'source' => $this->source instanceof PermissionsSetApproveAllSource ? $this->source->value : $this->source,
        ], fn ($value): bool => $value !== null);
    }
}
