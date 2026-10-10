<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Input policy context and ordered operations for managed permission evaluation. */
readonly class ManagedPermissionsEvaluateRequest implements Arrayable
{
    /**
     * @param  ManagedPermissionsContext|array  $context
     * @param  array<ManagedPermissionOperation|array>  $operations
     */
    public function __construct(
        public ManagedPermissionsContext|array $context,
        public array $operations = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            context: isset($data['context'])
                ? ($data['context'] instanceof ManagedPermissionsContext
                    ? $data['context']
                    : ManagedPermissionsContext::fromArray($data['context']))
                : ManagedPermissionsContext::fromArray([]),
            operations: array_map(
                static fn (ManagedPermissionOperation|array $operation) => $operation instanceof ManagedPermissionOperation
                    ? $operation
                    : ManagedPermissionOperation::fromArray($operation),
                $data['operations'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'context' => $this->context instanceof ManagedPermissionsContext
                ? $this->context->toArray()
                : $this->context,
            'operations' => array_map(
                static fn (ManagedPermissionOperation|array $operation) => $operation instanceof ManagedPermissionOperation
                    ? $operation->toArray()
                    : $operation,
                $this->operations,
            ),
        ];
    }
}
