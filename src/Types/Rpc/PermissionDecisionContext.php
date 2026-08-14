<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionDecisionOutcome;
use Revolution\Copilot\Enums\PermissionDecisionSource;
use Revolution\Copilot\Enums\PermissionDecisionSurface;

/**
 * Optional informational context describing how and where the permission decision
 * was made. This does not affect permission behavior.
 *
 * @experimental
 */
readonly class PermissionDecisionContext implements Arrayable
{
    public function __construct(
        public PermissionDecisionOutcome $outcome,
        public PermissionDecisionSource $source,
        public PermissionDecisionSurface $surface,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            outcome: PermissionDecisionOutcome::from(Arr::string($data, 'outcome')),
            source: PermissionDecisionSource::from(Arr::string($data, 'source')),
            surface: PermissionDecisionSurface::from(Arr::string($data, 'surface')),
        );
    }

    public function toArray(): array
    {
        return [
            'outcome' => $this->outcome->value,
            'source' => $this->source->value,
            'surface' => $this->surface->value,
        ];
    }
}
