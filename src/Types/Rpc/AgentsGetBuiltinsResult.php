<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Names and toggleability for agents shipped by the CLI runtime. */
readonly class AgentsGetBuiltinsResult implements Arrayable
{
    /**
     * @param  string[]  $names
     * @param  string[]  $disableableNames
     * @param  string[]  $yamlBasedNames
     */
    public function __construct(
        public array $names = [],
        public array $disableableNames = [],
        public array $yamlBasedNames = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            names: $data['names'] ?? [],
            disableableNames: $data['disableableNames'] ?? [],
            yamlBasedNames: $data['yamlBasedNames'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'names' => $this->names,
            'disableableNames' => $this->disableableNames,
            'yamlBasedNames' => $this->yamlBasedNames,
        ];
    }
}
