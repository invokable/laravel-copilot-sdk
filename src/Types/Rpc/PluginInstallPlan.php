<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Prepared Agent Plugin install plan. Nothing has been applied.
 *
 * @experimental
 */
readonly class PluginInstallPlan implements Arrayable
{
    public function __construct(
        public string $expiresAt,
        public string $operationId,
        public string $planHandle,
        public PluginInstallationReview $review,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            expiresAt: $data['expiresAt'],
            operationId: $data['operationId'],
            planHandle: $data['planHandle'],
            review: PluginInstallationReview::fromArray($data['review']),
        );
    }

    public function toArray(): array
    {
        return [
            'expiresAt' => $this->expiresAt,
            'operationId' => $this->operationId,
            'planHandle' => $this->planHandle,
            'review' => $this->review->toArray(),
        ];
    }
}
