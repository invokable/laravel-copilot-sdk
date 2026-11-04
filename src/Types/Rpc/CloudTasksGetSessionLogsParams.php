<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for reading cloud coding-agent session logs.
 *
 * @experimental
 */
readonly class CloudTasksGetSessionLogsParams implements Arrayable
{
    public function __construct(
        public mixed $authInfo,
        public string $sessionId,
        public ?string $authContextId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            authInfo: $data['authInfo'],
            authContextId: $data['authContextId'] ?? null,
            sessionId: $data['sessionId'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'authInfo' => $this->authInfo,
            'authContextId' => $this->authContextId,
            'sessionId' => $this->sessionId,
        ], fn ($value, $key): bool => $key === 'authInfo' || $value !== null, ARRAY_FILTER_USE_BOTH);
    }
}
