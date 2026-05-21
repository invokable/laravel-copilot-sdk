<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Context token breakdown result for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataContextInfoResult implements Arrayable
{
    public function __construct(
        public ?SessionContextInfo $contextInfo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contextInfo: isset($data['contextInfo']) && is_array($data['contextInfo'])
                ? SessionContextInfo::fromArray($data['contextInfo'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'contextInfo' => $this->contextInfo?->toArray(),
        ], fn ($value): bool => $value !== null);
    }
}
