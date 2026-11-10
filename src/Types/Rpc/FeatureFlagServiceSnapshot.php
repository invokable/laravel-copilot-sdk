<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Current feature-flag service state, without configuration documents or credentials.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FeatureFlagServiceSnapshot implements Arrayable
{
    /**
     * @param  array<string, bool>  $flags
     * @param  array<string, int|float>  $expFlags
     */
    public function __construct(
        public string $serviceId,
        public int $revision,
        public bool $active,
        public bool $expResponseResolved,
        public array $flags,
        public array $expFlags,
        public mixed $response,
        public bool $autoApprovalTargeted,
        public FeatureFlagReasoningSummariesArm|array $reasoningSummariesArm,
        public bool $gptDefaultModelEnabled,
        public ?string $assignmentContext = null,
        public ?string $secondaryAssignmentContext = null,
        public ?string $dynamicInstructionsRetrievalArm = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            serviceId: Arr::string($data, 'serviceId'),
            revision: Arr::integer($data, 'revision'),
            active: Arr::boolean($data, 'active'),
            expResponseResolved: Arr::boolean($data, 'expResponseResolved'),
            flags: Arr::array($data, 'flags', []),
            expFlags: Arr::array($data, 'expFlags', []),
            response: $data['response'] ?? null,
            assignmentContext: $data['assignmentContext'] ?? null,
            secondaryAssignmentContext: $data['secondaryAssignmentContext'] ?? null,
            autoApprovalTargeted: Arr::boolean($data, 'autoApprovalTargeted'),
            reasoningSummariesArm: $data['reasoningSummariesArm'] instanceof FeatureFlagReasoningSummariesArm
                ? $data['reasoningSummariesArm']
                : FeatureFlagReasoningSummariesArm::fromArray($data['reasoningSummariesArm']),
            gptDefaultModelEnabled: Arr::boolean($data, 'gptDefaultModelEnabled'),
            dynamicInstructionsRetrievalArm: $data['dynamicInstructionsRetrievalArm'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serviceId' => $this->serviceId,
            'revision' => $this->revision,
            'active' => $this->active,
            'expResponseResolved' => $this->expResponseResolved,
            'flags' => $this->flags,
            'expFlags' => $this->expFlags,
            'response' => $this->response,
            'assignmentContext' => $this->assignmentContext,
            'secondaryAssignmentContext' => $this->secondaryAssignmentContext,
            'autoApprovalTargeted' => $this->autoApprovalTargeted,
            'reasoningSummariesArm' => $this->reasoningSummariesArm instanceof FeatureFlagReasoningSummariesArm
                ? $this->reasoningSummariesArm->toArray()
                : $this->reasoningSummariesArm,
            'gptDefaultModelEnabled' => $this->gptDefaultModelEnabled,
            'dynamicInstructionsRetrievalArm' => $this->dynamicInstructionsRetrievalArm,
        ], static fn ($value, $key): bool => $value !== null || $key === 'response', ARRAY_FILTER_USE_BOTH);
    }
}
