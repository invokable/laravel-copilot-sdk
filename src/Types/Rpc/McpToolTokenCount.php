<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Context tokens one MCP tool definition costs.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpToolTokenCount implements Arrayable
{
    public function __construct(
        public string $name,
        public string $mcpToolName,
        public int $tokens,
        public bool $deferred,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name'),
            mcpToolName: Arr::string($data, 'mcpToolName'),
            tokens: Arr::integer($data, 'tokens'),
            deferred: Arr::boolean($data, 'deferred'),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'mcpToolName' => $this->mcpToolName,
            'tokens' => $this->tokens,
            'deferred' => $this->deferred,
        ];
    }
}
