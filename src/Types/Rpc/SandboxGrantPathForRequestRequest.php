<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to accept the sandbox path grant offered on an active sandbox escalation
 * permission prompt.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxGrantPathForRequestRequest implements Arrayable
{
    /**
     * @param  string  $requestId  Identifier of the exact pending sandbox escalation permission request
     *                             whose sandboxPathGrant to accept.
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
