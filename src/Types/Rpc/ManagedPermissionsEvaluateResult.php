<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Ordered managed-policy verdicts and fail-closed posture. */
readonly class ManagedPermissionsEvaluateResult implements Arrayable
{
    /** @param array<ManagedPermissionEvaluation|array> $results */
    public function __construct(
        public array $results = [],
        public bool $failClosed = false,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            results: array_map(
                static fn (ManagedPermissionEvaluation|array $result) => $result instanceof ManagedPermissionEvaluation
                    ? $result
                    : ManagedPermissionEvaluation::fromArray($result),
                $data['results'] ?? [],
            ),
            failClosed: (bool) ($data['failClosed'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'results' => array_map(
                static fn (ManagedPermissionEvaluation|array $result) => $result instanceof ManagedPermissionEvaluation
                    ? $result->toArray()
                    : $result,
                $this->results,
            ),
            'failClosed' => $this->failClosed,
        ];
    }
}
