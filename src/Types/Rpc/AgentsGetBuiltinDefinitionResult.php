<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class AgentsGetBuiltinDefinitionResult implements Arrayable
{
    public function __construct(public string $definitionJson) {}

    public static function fromArray(array $data): self
    {
        return new self(definitionJson: Arr::string($data, 'definitionJson', ''));
    }

    public function toArray(): array
    {
        return ['definitionJson' => $this->definitionJson];
    }
}
