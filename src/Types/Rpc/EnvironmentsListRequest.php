<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\EnvironmentKind;

/**
 * Optional discovery filters supported by GitHub Mission Control.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnvironmentsListRequest implements Arrayable
{
    /**
     * @param  EnvironmentKind|null  $kind  Restrict discovery to this compute kind.
     * @param  string|null  $status  Operational status, such as online, offline, degraded, waking, or draining.
     */
    public function __construct(
        public ?EnvironmentKind $kind = null,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: isset($data['kind']) ? EnvironmentKind::from($data['kind']) : null,
            status: isset($data['status']) ? (string) $data['status'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind?->value,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }
}
