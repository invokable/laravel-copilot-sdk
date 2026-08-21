<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanPolicyDecision;
use Revolution\Copilot\Enums\McpPlanPolicySource;

/**
 * Outcome of evaluating the planned server against registry and enterprise policy.
 *
 * @experimental
 */
readonly class McpPlanPolicyResult implements Arrayable
{
    public function __construct(
        public McpPlanPolicyDecision $decision,
        public McpPlanPolicySource $source,
        public ?string $reason = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            decision: McpPlanPolicyDecision::from($data['decision']),
            source: McpPlanPolicySource::from($data['source']),
            reason: $data['reason'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'decision' => $this->decision->value,
            'source' => $this->source->value,
        ];
        if ($this->reason !== null) {
            $arr['reason'] = $this->reason;
        }

        return $arr;
    }
}
