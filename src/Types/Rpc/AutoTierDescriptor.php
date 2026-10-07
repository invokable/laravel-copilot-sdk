<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A server-advertised Auto routing preference.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AutoTierDescriptor implements Arrayable
{
    public function __construct(
        public string $description,
        public string $displayName,
        public string $id,
        public AutoTierStatus $status,
        public string $type,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            description: Arr::string($data, 'description'),
            displayName: Arr::string($data, 'displayName'),
            id: Arr::string($data, 'id'),
            status: AutoTierStatus::fromArray(Arr::array($data, 'status')),
            type: Arr::string($data, 'type'),
        );
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'displayName' => $this->displayName,
            'id' => $this->id,
            'status' => $this->status->toArray(),
            'type' => $this->type,
        ];
    }
}
