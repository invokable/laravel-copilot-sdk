<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Agent content to render without file access. */
readonly class AgentsGenerateFileContentRequest implements Arrayable
{
    /**
     * @param  string[]|null  $tools
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $instructions,
        public ?array $tools = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            description: Arr::string($data, 'description'),
            tools: isset($data['tools']) ? Arr::array($data, 'tools') : null,
            instructions: Arr::string($data, 'instructions'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'tools' => $this->tools,
            'instructions' => $this->instructions,
        ], static fn ($value): bool => $value !== null);
    }
}
