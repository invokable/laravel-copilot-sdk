<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class BuiltinAgentSummary implements Arrayable
{
    public function __construct(
        public string $name,
        public string $description = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            description: Arr::string($data, 'description', ''),
        );
    }

    public function toArray(): array
    {
        return ['name' => $this->name, 'description' => $this->description];
    }
}
