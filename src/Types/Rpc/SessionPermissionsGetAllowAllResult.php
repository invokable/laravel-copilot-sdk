<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Effective permission escalation and the original launch baseline.
 *
 * @experimental
 */
readonly class SessionPermissionsGetAllowAllResult implements Arrayable
{
    public function __construct(
        public bool $active,
        public bool $autoApproval,
        public bool $storedAutoApproval,
        public bool $runtimeOverride,
        public PermissionsAllowAllBaseline $baseline,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            active: Arr::boolean($data, 'active'),
            autoApproval: Arr::boolean($data, 'autoApproval'),
            storedAutoApproval: Arr::boolean($data, 'storedAutoApproval'),
            runtimeOverride: Arr::boolean($data, 'runtimeOverride'),
            baseline: PermissionsAllowAllBaseline::fromArray(Arr::array($data, 'baseline')),
        );
    }

    public function toArray(): array
    {
        return [
            'active' => $this->active,
            'autoApproval' => $this->autoApproval,
            'storedAutoApproval' => $this->storedAutoApproval,
            'runtimeOverride' => $this->runtimeOverride,
            'baseline' => $this->baseline->toArray(),
        ];
    }
}
