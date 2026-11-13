<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Classifier outcomes and optional trajectory validation diagnostics.
 *
 * @experimental Classification output is an untrusted measurement, not a security decision.
 */
readonly class TaskAnalyticsClassifyTrajectoryResult implements Arrayable
{
    /**
     * @param  array<int, TaskAnalyticsTrajectoryClassification>  $classifications
     */
    public function __construct(
        public array $classifications,
        public ?string $trajectoryFingerprint = null,
        public ?string $interactionId = null,
        public ?TaskAnalyticsTrajectoryValidation $validation = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $classifications = array_map(
            fn (array $classification) => TaskAnalyticsTrajectoryClassification::fromArray($classification),
            Arr::array($data, 'classifications', []),
        );
        $validation = $data['validation'] ?? null;

        return new self(
            classifications: $classifications,
            trajectoryFingerprint: isset($data['trajectoryFingerprint']) ? Arr::string($data, 'trajectoryFingerprint') : null,
            interactionId: isset($data['interactionId']) ? Arr::string($data, 'interactionId') : null,
            validation: is_array($validation) ? TaskAnalyticsTrajectoryValidation::fromArray($validation) : $validation,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'classifications' => array_map(
                fn (TaskAnalyticsTrajectoryClassification $classification) => $classification->toArray(),
                $this->classifications,
            ),
            'trajectoryFingerprint' => $this->trajectoryFingerprint,
            'interactionId' => $this->interactionId,
            'validation' => $this->validation?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
