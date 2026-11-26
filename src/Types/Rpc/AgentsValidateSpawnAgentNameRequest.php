<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class AgentsValidateSpawnAgentNameRequest implements Arrayable
{
    /**
     * @param  SpawnValidatableAgent[]  $customAgents
     * @param  string[]  $builtinNames
     */
    public function __construct(
        public string $name,
        public array $customAgents,
        public array $builtinNames,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            customAgents: array_map(
                fn (array $agent) => SpawnValidatableAgent::fromArray($agent),
                Arr::array($data, 'customAgents'),
            ),
            builtinNames: Arr::array($data, 'builtinNames'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'customAgents' => array_map(fn (SpawnValidatableAgent $agent) => $agent->toArray(), $this->customAgents),
            'builtinNames' => $this->builtinNames,
        ];
    }
}
