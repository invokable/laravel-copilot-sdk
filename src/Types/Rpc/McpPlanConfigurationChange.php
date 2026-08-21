<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanConfigurationOperation;
use Revolution\Copilot\Enums\McpPlanScope;

/**
 * One change applying the plan would make.
 *
 * @experimental
 */
readonly class McpPlanConfigurationChange implements Arrayable
{
    /**
     * @param  string[]  $changedFields
     * @param  string[]  $secretReferences
     */
    public function __construct(
        public McpPlanConfigurationOperation $operation,
        public McpPlanScope $scope,
        public string $configKey,
        public array $changedFields,
        public array $secretReferences,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            operation: McpPlanConfigurationOperation::from($data['operation']),
            scope: McpPlanScope::from($data['scope']),
            configKey: $data['configKey'],
            changedFields: $data['changedFields'] ?? [],
            secretReferences: $data['secretReferences'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'operation' => $this->operation->value,
            'scope' => $this->scope->value,
            'configKey' => $this->configKey,
            'changedFields' => $this->changedFields,
            'secretReferences' => $this->secretReferences,
        ];
    }
}
