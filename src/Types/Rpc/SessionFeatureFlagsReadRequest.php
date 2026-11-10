<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Evaluations and readiness barriers for a feature-flag snapshot.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionFeatureFlagsReadRequest implements Arrayable
{
    /**
     * @param  string[]|null  $flags
     * @param  array<FeatureFlagExpOverrideQuery|array>|null  $overrides
     */
    public function __construct(
        public ?bool $waitForExpResponse = null,
        public ?array $flags = null,
        public ?array $overrides = null,
        public ?int $afterRevision = null,
        public ?bool $displayOnly = null,
        public ?string $serviceId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            waitForExpResponse: isset($data['waitForExpResponse']) ? Arr::boolean($data, 'waitForExpResponse') : null,
            flags: isset($data['flags']) ? Arr::array($data, 'flags') : null,
            overrides: isset($data['overrides']) ? Arr::array($data, 'overrides') : null,
            afterRevision: isset($data['afterRevision']) ? Arr::integer($data, 'afterRevision') : null,
            displayOnly: isset($data['displayOnly']) ? Arr::boolean($data, 'displayOnly') : null,
            serviceId: isset($data['serviceId']) ? Arr::string($data, 'serviceId') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'waitForExpResponse' => $this->waitForExpResponse,
            'flags' => $this->flags,
            'overrides' => $this->overrides === null
                ? null
                : array_map(
                    static fn (FeatureFlagExpOverrideQuery|array $override): array => $override instanceof FeatureFlagExpOverrideQuery
                        ? $override->toArray()
                        : $override,
                    $this->overrides,
                ),
            'afterRevision' => $this->afterRevision,
            'displayOnly' => $this->displayOnly,
            'serviceId' => $this->serviceId,
        ], static fn ($value): bool => $value !== null);
    }
}
