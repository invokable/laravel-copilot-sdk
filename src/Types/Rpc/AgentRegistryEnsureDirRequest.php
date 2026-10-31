<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The location of a local agent registry.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentRegistryEnsureDirRequest implements Arrayable
{
    public function __construct(
        public string $homeDir,
        public ?string $copilotHome = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            homeDir: Arr::string($data, 'homeDir'),
            copilotHome: isset($data['copilotHome']) ? Arr::string($data, 'copilotHome') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'copilotHome' => $this->copilotHome,
            'homeDir' => $this->homeDir,
        ], fn ($value) => $value !== null);
    }
}
