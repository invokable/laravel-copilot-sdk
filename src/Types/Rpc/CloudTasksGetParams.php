<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for reading a cloud coding-agent task.
 *
 * @experimental
 */
readonly class CloudTasksGetParams implements Arrayable
{
    public function __construct(
        public mixed $authInfo,
        public string $integrationId,
        public string $taskId,
        public ?string $authContextId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            authInfo: $data['authInfo'],
            authContextId: $data['authContextId'] ?? null,
            integrationId: $data['integrationId'],
            taskId: $data['taskId'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'authInfo' => $this->authInfo,
            'authContextId' => $this->authContextId,
            'integrationId' => $this->integrationId,
            'taskId' => $this->taskId,
        ], fn ($value, $key): bool => $key === 'authInfo' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }
}
