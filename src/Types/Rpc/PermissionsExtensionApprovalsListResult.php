<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class PermissionsExtensionApprovalsListResult implements Arrayable
{
    /** @param  array<PermissionsExtensionApproval>  $approvals */
    public function __construct(
        public array $approvals,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            approvals: array_map(
                fn (array $approval): PermissionsExtensionApproval => PermissionsExtensionApproval::fromArray($approval),
                Arr::array($data, 'approvals'),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'approvals' => array_map(
                fn (PermissionsExtensionApproval $approval): array => $approval->toArray(),
                $this->approvals,
            ),
        ];
    }
}
