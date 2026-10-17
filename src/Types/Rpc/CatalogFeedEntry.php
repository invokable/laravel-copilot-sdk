<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CatalogFeedEntryStatus;
use Revolution\Copilot\Enums\CatalogFeedUnavailableReason;

/**
 * One ordered entry in a saved or generated catalog feed.
 *
 * @experimental
 */
readonly class CatalogFeedEntry implements Arrayable
{
    public function __construct(
        public CatalogFeedEntryStatus $status,
        public string $identifier,
        public CatalogMcpServerCandidate|CatalogAiSkillCandidate|CatalogAgentPluginCandidate|null $candidate = null,
        public ?CatalogFeedUnavailableReason $reason = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $status = CatalogFeedEntryStatus::from($data['status']);
        $candidateData = $data['candidate'] ?? null;
        $candidate = $candidateData === null ? null : match ($candidateData['kind'] ?? '') {
            'mcp-server' => CatalogMcpServerCandidate::fromArray($candidateData),
            'plugin' => CatalogAgentPluginCandidate::fromArray($candidateData),
            default => CatalogAiSkillCandidate::fromArray($candidateData),
        };

        return new static(
            status: $status,
            identifier: $data['identifier'],
            candidate: $candidate,
            reason: isset($data['reason']) ? CatalogFeedUnavailableReason::from($data['reason']) : null,
        );
    }

    public function toArray(): array
    {
        $data = [
            'status' => $this->status->value,
            'identifier' => $this->identifier,
        ];

        if ($this->candidate !== null) {
            $data['candidate'] = $this->candidate->toArray();
        }
        if ($this->reason !== null) {
            $data['reason'] = $this->reason->value;
        }

        return $data;
    }
}
