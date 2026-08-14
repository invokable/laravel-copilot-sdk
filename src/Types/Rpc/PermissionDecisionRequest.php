<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Support\PermissionDecision;

/**
 * Parameters for handling a pending permission request.
 *
 * The result array must contain a "kind" key matching the generated
 * PermissionDecision union, such as "approve-once", "approve-for-session",
 * "approve-permanently", "reject", or "user-not-available".
 *
 * Use {@see PermissionDecision} for building result arrays.
 */
readonly class PermissionDecisionRequest implements Arrayable
{
    /**
     * @param  string  $requestId  The ID of the pending permission request to handle
     * @param  array  $result  Permission decision result; see class docblock for structure
     * @param  PermissionDecisionContext|null  $decisionContext  Optional informational context describing how and where the decision was made. Never changes permission behavior.
     */
    public function __construct(
        public string $requestId,
        public array $result,
        public ?PermissionDecisionContext $decisionContext = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId'),
            result: Arr::array($data, 'result'),
            decisionContext: isset($data['decisionContext']) ? PermissionDecisionContext::fromArray($data['decisionContext']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'result' => $this->result,
            'decisionContext' => $this->decisionContext?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
