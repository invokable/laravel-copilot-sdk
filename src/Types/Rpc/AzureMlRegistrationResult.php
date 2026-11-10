<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\AzureMlRegistrationKind;

/**
 * Observable outcome of a scoped registration change.
 *
 * @experimental
 */
readonly class AzureMlRegistrationResult implements Arrayable
{
    public function __construct(
        public AzureMlRegistrationKind|string $kind,
        public string $path,
        public ?string $detail = null,
        public mixed $before = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $kind = $data['kind'] ?? '';

        return new self(
            kind: is_string($kind) ? (AzureMlRegistrationKind::tryFrom($kind) ?? $kind) : '',
            path: $data['path'] ?? '',
            detail: $data['detail'] ?? null,
            before: $data['before'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'kind' => $this->kind instanceof AzureMlRegistrationKind ? $this->kind->value : $this->kind,
            'path' => $this->path,
            'detail' => $this->detail,
            'before' => $this->before,
        ], static fn ($value) => $value !== null);
    }
}
