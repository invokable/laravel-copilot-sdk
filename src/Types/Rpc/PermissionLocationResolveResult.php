<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PermissionLocationType;

/**
 * Resolved permissions location.
 */
readonly class PermissionLocationResolveResult implements Arrayable
{
    public function __construct(
        public string $locationKey,
        public PermissionLocationType|string $locationType,
    ) {}

    public static function fromArray(array $data): self
    {
        $locationType = Arr::string($data, 'locationType');

        return new self(
            locationKey: Arr::string($data, 'locationKey'),
            locationType: PermissionLocationType::tryFrom($locationType) ?? $locationType,
        );
    }

    public function toArray(): array
    {
        return [
            'locationKey' => $this->locationKey,
            'locationType' => $this->locationType instanceof PermissionLocationType ? $this->locationType->value : $this->locationType,
        ];
    }
}
