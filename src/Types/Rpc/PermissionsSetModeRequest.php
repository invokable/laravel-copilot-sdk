<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionMode;
use Revolution\Copilot\Enums\PermissionModeSource;

/**
 * Permission mode to apply for the session.
 *
 * @experimental
 */
readonly class PermissionsSetModeRequest implements Arrayable
{
    /**
     * @param  PermissionMode  $mode  Permission mode to apply. `manual` follows the normal approval flow;
     *                                `assisted` attaches LLM safety recommendations; `allow-all` automatically
     *                                approves permission requests.
     * @param  string|null  $assistedApprovalModel  Optional judge model id for assisted mode. When omitted,
     *                                               the session resolves the provider default: `gpt-5.5` for
     *                                               CAPI sessions and the active session model for BYOK sessions.
     * @param  PermissionModeSource|string|null  $source  Optional source for permission-mode telemetry.
     */
    public function __construct(
        public PermissionMode $mode,
        public ?string $assistedApprovalModel = null,
        public PermissionModeSource|string|null $source = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            mode: PermissionMode::from($data['mode']),
            assistedApprovalModel: Arr::get($data, 'assistedApprovalModel'),
            source: isset($data['source']) ? PermissionModeSource::tryFrom($data['source']) ?? $data['source'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode->value,
            'assistedApprovalModel' => $this->assistedApprovalModel,
            'source' => $this->source instanceof PermissionModeSource ? $this->source->value : $this->source,
        ], fn ($value): bool => $value !== null);
    }
}
