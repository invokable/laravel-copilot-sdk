<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Raw content-exclusion policy JSON to validate. */
readonly class ContentExclusionValidatePoliciesRequest implements Arrayable
{
    public function __construct(
        public string $policiesJson,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            policiesJson: Arr::string($data, 'policiesJson'),
        );
    }

    public function toArray(): array
    {
        return [
            'policiesJson' => $this->policiesJson,
        ];
    }
}
