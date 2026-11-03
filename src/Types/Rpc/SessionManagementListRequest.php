<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Lists opted-in local peers. The caller is omitted.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementListRequest implements Arrayable
{
    public function __construct(
        public ?string $cwd = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cwd: $data['cwd'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'cwd' => $this->cwd,
        ], fn ($value): bool => $value !== null);
    }
}
