<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Evaluation inputs for the bound session's feature-flag service.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionFeatureFlagsConfigureRequest implements Arrayable
{
    /**
     * @param  array<string, bool>|null  $flagOverrides
     * @param  array<string, bool>|null  $initialSessionFlags
     * @param  array<string, int|float>|null  $expFlagOverrides
     * @param  array<string, string>|null  $environment
     */
    public function __construct(
        public bool $isStaff,
        public bool $isExperimental,
        public bool $isTeam,
        public bool $online,
        public bool $streamerMode,
        public ?bool $isStaffGithub = null,
        public ?bool $isStaffMicrosoft = null,
        public mixed $config = null,
        public ?array $flagOverrides = null,
        public ?array $initialSessionFlags = null,
        public ?array $expFlagOverrides = null,
        public ?array $environment = null,
        public FeatureFlagAssignmentsUpdate|array|null $assignments = null,
        public ?int $firstLaunchAtMillis = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            isStaff: Arr::boolean($data, 'isStaff'),
            isExperimental: Arr::boolean($data, 'isExperimental'),
            isTeam: Arr::boolean($data, 'isTeam'),
            online: Arr::boolean($data, 'online'),
            streamerMode: Arr::boolean($data, 'streamerMode'),
            isStaffGithub: isset($data['isStaffGithub']) ? Arr::boolean($data, 'isStaffGithub') : null,
            isStaffMicrosoft: isset($data['isStaffMicrosoft']) ? Arr::boolean($data, 'isStaffMicrosoft') : null,
            config: $data['config'] ?? null,
            flagOverrides: isset($data['flagOverrides']) ? Arr::array($data, 'flagOverrides') : null,
            initialSessionFlags: isset($data['initialSessionFlags']) ? Arr::array($data, 'initialSessionFlags') : null,
            expFlagOverrides: isset($data['expFlagOverrides']) ? Arr::array($data, 'expFlagOverrides') : null,
            environment: isset($data['environment']) ? Arr::array($data, 'environment') : null,
            assignments: isset($data['assignments'])
                ? ($data['assignments'] instanceof FeatureFlagAssignmentsUpdate
                    ? $data['assignments']
                    : FeatureFlagAssignmentsUpdate::fromArray($data['assignments']))
                : null,
            firstLaunchAtMillis: isset($data['firstLaunchAtMillis']) ? Arr::integer($data, 'firstLaunchAtMillis') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'isStaff' => $this->isStaff,
            'isExperimental' => $this->isExperimental,
            'isTeam' => $this->isTeam,
            'online' => $this->online,
            'streamerMode' => $this->streamerMode,
            'isStaffGithub' => $this->isStaffGithub,
            'isStaffMicrosoft' => $this->isStaffMicrosoft,
            'config' => $this->config,
            'flagOverrides' => $this->flagOverrides,
            'initialSessionFlags' => $this->initialSessionFlags,
            'expFlagOverrides' => $this->expFlagOverrides,
            'environment' => $this->environment,
            'assignments' => $this->assignments instanceof FeatureFlagAssignmentsUpdate
                ? $this->assignments->toArray()
                : $this->assignments,
            'firstLaunchAtMillis' => $this->firstLaunchAtMillis,
        ], static fn ($value): bool => $value !== null);
    }
}
