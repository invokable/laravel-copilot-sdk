<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Advice for a sandbox path grant, when it needs any.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxPathGrantWarningResult implements Arrayable
{
    public function __construct(
        public ?string $warning = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            warning: isset($data['warning']) ? Arr::string($data, 'warning') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'warning' => $this->warning,
        ], fn ($value) => $value !== null);
    }
}
