<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to disable sandboxing for the current session while resolving an active
 * sandbox-bypass permission prompt.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxDisableForSessionRequest implements Arrayable
{
    /**
     * @param  string  $requestId  Identifier of the exact pending sandbox-bypass permission request
     *                             that authorized the session opt-out.
     * @param  PermissionDecisionContext|array|null  $decisionContext  Optional informational context describing
     *                                                                 how and where the decision was made.
     */
    public function __construct(
        public string $requestId,
        public PermissionDecisionContext|array|null $decisionContext = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId'),
            decisionContext: isset($data['decisionContext'])
                ? ($data['decisionContext'] instanceof PermissionDecisionContext ? $data['decisionContext'] : PermissionDecisionContext::fromArray($data['decisionContext']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'decisionContext' => $this->decisionContext instanceof PermissionDecisionContext
                ? $this->decisionContext->toArray()
                : $this->decisionContext,
        ], fn ($value) => $value !== null);
    }
}
