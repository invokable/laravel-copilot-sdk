<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A secret value for an MCP installation placeholder.
 *
 * @experimental
 */
readonly class McpInstallationSecret implements Arrayable
{
    public function __construct(
        public string $placeholder,
        public string $value,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            placeholder: $data['placeholder'],
            value: $data['value'],
        );
    }

    public function toArray(): array
    {
        return [
            'placeholder' => $this->placeholder,
            'value' => $this->value,
        ];
    }
}
