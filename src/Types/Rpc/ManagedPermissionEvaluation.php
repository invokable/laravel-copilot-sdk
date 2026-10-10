<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ManagedPermissionVerdict;

/** Managed-policy verdict for one ordered operation. */
readonly class ManagedPermissionEvaluation implements Arrayable
{
    public function __construct(
        public ManagedPermissionOperation|array $operation,
        public ManagedPermissionVerdict|string $verdict,
    ) {}

    public static function fromArray(array $data): self
    {
        $verdict = $data['verdict'] ?? 'unmanaged';

        return new self(
            operation: isset($data['operation'])
                ? ($data['operation'] instanceof ManagedPermissionOperation
                    ? $data['operation']
                    : ManagedPermissionOperation::fromArray($data['operation']))
                : ManagedPermissionOperation::fromArray([]),
            verdict: ManagedPermissionVerdict::tryFrom($verdict) ?? $verdict,
        );
    }

    public function toArray(): array
    {
        return [
            'operation' => $this->operation instanceof ManagedPermissionOperation
                ? $this->operation->toArray()
                : $this->operation,
            'verdict' => $this->verdict instanceof ManagedPermissionVerdict
                ? $this->verdict->value
                : $this->verdict,
        ];
    }
}
