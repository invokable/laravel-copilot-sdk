<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Enables or disables a skill by name in the global skills configuration.
 *
 * @experimental
 */
readonly class SkillsConfigSetSkillDisabledRequest implements Arrayable
{
    public function __construct(
        public string $name,
        public bool $disabled,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            name: $data['name'],
            disabled: $data['disabled'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'disabled' => $this->disabled,
        ];
    }
}
