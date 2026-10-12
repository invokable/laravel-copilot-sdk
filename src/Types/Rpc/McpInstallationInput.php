<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanValueCategory;

/**
 * A non-secret value supplied for an MCP installation.
 *
 * @experimental
 */
readonly class McpInstallationInput implements Arrayable
{
    public function __construct(
        public McpPlanValueCategory $category,
        public string $key,
        public string $value,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            category: McpPlanValueCategory::from($data['category']),
            key: $data['key'],
            value: $data['value'],
        );
    }

    public function toArray(): array
    {
        return [
            'category' => $this->category->value,
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
