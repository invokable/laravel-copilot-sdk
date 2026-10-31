<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Options for local agent discovery.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryListEntriesRequest implements Arrayable
{
    public function __construct(
        public string $homeDir,
        public ?string $copilotHome = null,
        public ?int $excludePid = null,
        public ?int $staleMs = null,
        public ?int $nowMs = null,
        public ?bool $pidAliveOverride = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            homeDir: Arr::string($data, 'homeDir'),
            copilotHome: isset($data['copilotHome']) ? Arr::string($data, 'copilotHome') : null,
            excludePid: isset($data['excludePid']) ? Arr::integer($data, 'excludePid') : null,
            staleMs: isset($data['staleMs']) ? Arr::integer($data, 'staleMs') : null,
            nowMs: isset($data['nowMs']) ? Arr::integer($data, 'nowMs') : null,
            pidAliveOverride: isset($data['pidAliveOverride']) ? Arr::boolean($data, 'pidAliveOverride') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'copilotHome' => $this->copilotHome,
            'homeDir' => $this->homeDir,
            'excludePid' => $this->excludePid,
            'staleMs' => $this->staleMs,
            'nowMs' => $this->nowMs,
            'pidAliveOverride' => $this->pidAliveOverride,
        ], fn ($value) => $value !== null);
    }
}
