<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionLocationType;

/**
 * Result of applying permissions stored for a location.
 */
readonly class PermissionLocationApplyResult implements Arrayable
{
    /**
     * @param  array<int, array<string, mixed>>  $appliedRules
     */
    public function __construct(
        public int $appliedDirectoryCount,
        public int $appliedRuleCount,
        public array $appliedRules,
        public bool $changed,
        public string $locationKey,
        public PermissionLocationType|string $locationType,
    ) {}

    public static function fromArray(array $data): self
    {
        $locationType = Arr::string($data, 'locationType');

        return new self(
            appliedDirectoryCount: Arr::integer($data, 'appliedDirectoryCount', 0),
            appliedRuleCount: Arr::integer($data, 'appliedRuleCount', 0),
            appliedRules: $data['appliedRules'] ?? [],
            changed: Arr::boolean($data, 'changed', false),
            locationKey: Arr::string($data, 'locationKey'),
            locationType: PermissionLocationType::tryFrom($locationType) ?? $locationType,
        );
    }

    public function toArray(): array
    {
        return [
            'appliedDirectoryCount' => $this->appliedDirectoryCount,
            'appliedRuleCount' => $this->appliedRuleCount,
            'appliedRules' => $this->appliedRules,
            'changed' => $this->changed,
            'locationKey' => $this->locationKey,
            'locationType' => $this->locationType instanceof PermissionLocationType ? $this->locationType->value : $this->locationType,
        ];
    }
}
