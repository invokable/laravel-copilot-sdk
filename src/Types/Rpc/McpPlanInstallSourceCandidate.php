<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanInstallSourceCandidateKind;

/**
 * Plan from a candidate returned by a previous catalog search.
 *
 * @experimental
 */
readonly class McpPlanInstallSourceCandidate implements Arrayable
{
    public function __construct(
        public McpPlanInstallSourceCandidateKind $kind,
        public string $candidateHandle,
        public string $searchId,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: McpPlanInstallSourceCandidateKind::from($data['kind']),
            candidateHandle: $data['candidateHandle'],
            searchId: $data['searchId'],
        );
    }

    public function toArray(): array
    {
        return [
            'kind' => $this->kind->value,
            'candidateHandle' => $this->candidateHandle,
            'searchId' => $this->searchId,
        ];
    }
}
