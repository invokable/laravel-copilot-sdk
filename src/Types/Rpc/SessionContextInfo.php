<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Token-usage breakdown for a session context window.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionContextInfo implements Arrayable
{
    public function __construct(
        public int $bufferTokens,
        public int $compactionThreshold,
        public int $conversationTokens,
        public int $limit,
        public string $modelName,
        public int $promptTokenLimit,
        public int $systemTokens,
        public int $toolDefinitionsTokens,
        public int $mcpToolsTokens,
        public int $totalTokens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bufferTokens: Arr::integer($data, 'bufferTokens', 0),
            compactionThreshold: Arr::integer($data, 'compactionThreshold', 0),
            conversationTokens: Arr::integer($data, 'conversationTokens', 0),
            limit: Arr::integer($data, 'limit', 0),
            modelName: Arr::string($data, 'modelName', ''),
            promptTokenLimit: Arr::integer($data, 'promptTokenLimit', 0),
            systemTokens: Arr::integer($data, 'systemTokens', 0),
            toolDefinitionsTokens: Arr::integer($data, 'toolDefinitionsTokens', 0),
            mcpToolsTokens: Arr::integer($data, 'mcpToolsTokens', 0),
            totalTokens: Arr::integer($data, 'totalTokens', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'bufferTokens' => $this->bufferTokens,
            'compactionThreshold' => $this->compactionThreshold,
            'conversationTokens' => $this->conversationTokens,
            'limit' => $this->limit,
            'modelName' => $this->modelName,
            'promptTokenLimit' => $this->promptTokenLimit,
            'systemTokens' => $this->systemTokens,
            'toolDefinitionsTokens' => $this->toolDefinitionsTokens,
            'mcpToolsTokens' => $this->mcpToolsTokens,
            'totalTokens' => $this->totalTokens,
        ];
    }
}
