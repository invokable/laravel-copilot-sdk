<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A local agent entry identified by its process identifier.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryRemoveEntryRequest implements Arrayable
{
    public function __construct(
        public string $homeDir,
        public int $pid,
        public ?string $copilotHome = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            homeDir: Arr::string($data, 'homeDir'),
            pid: Arr::integer($data, 'pid'),
            copilotHome: isset($data['copilotHome']) ? Arr::string($data, 'copilotHome') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'copilotHome' => $this->copilotHome,
            'homeDir' => $this->homeDir,
            'pid' => $this->pid,
        ], fn ($value) => $value !== null);
    }
}
