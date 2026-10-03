<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** Request a rendered MCP prompt. */
readonly class McpPromptsGetRequest implements Arrayable
{
    /** @param array<string, string>|null $arguments */
    public function __construct(
        public string $serverName,
        public string $promptName,
        public ?array $arguments = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            serverName: Arr::string($data, 'serverName', ''),
            promptName: Arr::string($data, 'promptName', ''),
            arguments: $data['arguments'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'serverName' => $this->serverName,
            'promptName' => $this->promptName,
            'arguments' => $this->arguments,
        ], fn ($value) => $value !== null);
    }
}
