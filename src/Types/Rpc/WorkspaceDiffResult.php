<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Workspace diff result for the requested mode.
 *
 * @experimental
 */
readonly class WorkspaceDiffResult implements Arrayable
{
    /**
     * @param  array<WorkspaceDiffFileChange>  $changes
     */
    public function __construct(
        public array $changes,
        public bool $isFallback,
        public string $mode,
        public string $requestedMode,
        public ?string $baseBranch = null,
        public ?string $unavailableReason = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            baseBranch: $data['baseBranch'] ?? null,
            changes: array_map(
                static fn (array $change): WorkspaceDiffFileChange => WorkspaceDiffFileChange::fromArray($change),
                $data['changes'] ?? [],
            ),
            isFallback: (bool) ($data['isFallback'] ?? false),
            mode: $data['mode'] ?? '',
            requestedMode: $data['requestedMode'] ?? '',
            unavailableReason: $data['unavailableReason'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'baseBranch' => $this->baseBranch,
            'changes' => array_map(
                static fn (WorkspaceDiffFileChange $change): array => $change->toArray(),
                $this->changes,
            ),
            'isFallback' => $this->isFallback,
            'mode' => $this->mode,
            'requestedMode' => $this->requestedMode,
            'unavailableReason' => $this->unavailableReason,
        ], static fn ($value) => $value !== null);
    }
}
