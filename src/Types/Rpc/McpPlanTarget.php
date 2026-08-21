<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanScope;

/**
 * Where a plan would be written.
 *
 * @experimental
 */
readonly class McpPlanTarget implements Arrayable
{
    public function __construct(
        public McpPlanScope $scope,
        public string $configKey,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            scope: McpPlanScope::from($data['scope']),
            configKey: $data['configKey'],
        );
    }

    public function toArray(): array
    {
        return [
            'scope' => $this->scope->value,
            'configKey' => $this->configKey,
        ];
    }
}
