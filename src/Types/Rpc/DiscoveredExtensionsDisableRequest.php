<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Source-qualified extension identifiers to persistently disable.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class DiscoveredExtensionsDisableRequest implements Arrayable
{
    public function __construct(public array $ids) {}

    public static function fromArray(array $data): self
    {
        return new self(ids: $data['ids'] ?? []);
    }

    public function toArray(): array
    {
        return ['ids' => $this->ids];
    }
}
