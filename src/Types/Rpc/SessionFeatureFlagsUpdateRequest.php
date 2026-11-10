<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Ordered patch operations for a session's feature-flag service.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionFeatureFlagsUpdateRequest implements Arrayable
{
    public function __construct(
        public FeatureFlagConfigPatch|array|null $configPatch = null,
        public FeatureFlagEnabledOverride|array|null $enabledOverride = null,
        public FeatureFlagAssignmentsUpdate|array|null $assignments = null,
        public ?string $secondaryAssignmentContext = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            configPatch: isset($data['configPatch'])
                ? ($data['configPatch'] instanceof FeatureFlagConfigPatch
                    ? $data['configPatch']
                    : FeatureFlagConfigPatch::fromArray($data['configPatch']))
                : null,
            enabledOverride: isset($data['enabledOverride'])
                ? ($data['enabledOverride'] instanceof FeatureFlagEnabledOverride
                    ? $data['enabledOverride']
                    : FeatureFlagEnabledOverride::fromArray($data['enabledOverride']))
                : null,
            assignments: isset($data['assignments'])
                ? ($data['assignments'] instanceof FeatureFlagAssignmentsUpdate
                    ? $data['assignments']
                    : FeatureFlagAssignmentsUpdate::fromArray($data['assignments']))
                : null,
            secondaryAssignmentContext: $data['secondaryAssignmentContext'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'configPatch' => $this->configPatch instanceof FeatureFlagConfigPatch
                ? $this->configPatch->toArray()
                : $this->configPatch,
            'enabledOverride' => $this->enabledOverride instanceof FeatureFlagEnabledOverride
                ? $this->enabledOverride->toArray()
                : $this->enabledOverride,
            'assignments' => $this->assignments instanceof FeatureFlagAssignmentsUpdate
                ? $this->assignments->toArray()
                : $this->assignments,
            'secondaryAssignmentContext' => $this->secondaryAssignmentContext,
        ], static fn ($value): bool => $value !== null);
    }
}
