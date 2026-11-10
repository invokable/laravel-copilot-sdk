<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A decision model available to the resolved account.
 *
 * @experimental
 */
readonly class DecisionModelInfo implements Arrayable
{
    /**
     * @param  array<string, int>  $limits
     */
    public function __construct(
        public string $id,
        public string $name,
        public bool $isDefault,
        public array $limits,
        public bool $supportsRefusals,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            isDefault: $data['isDefault'],
            limits: $data['limits'],
            supportsRefusals: $data['supportsRefusals'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isDefault' => $this->isDefault,
            'limits' => $this->limits,
            'supportsRefusals' => $this->supportsRefusals,
        ];
    }
}
