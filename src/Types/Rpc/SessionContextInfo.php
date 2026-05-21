<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

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
        public int $totalTokens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bufferTokens: $data['bufferTokens'] ?? 0,
            compactionThreshold: $data['compactionThreshold'] ?? 0,
            conversationTokens: $data['conversationTokens'] ?? 0,
            limit: $data['limit'] ?? 0,
            modelName: $data['modelName'] ?? '',
            promptTokenLimit: $data['promptTokenLimit'] ?? 0,
            systemTokens: $data['systemTokens'] ?? 0,
            toolDefinitionsTokens: $data['toolDefinitionsTokens'] ?? 0,
            totalTokens: $data['totalTokens'] ?? 0,
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
            'totalTokens' => $this->totalTokens,
        ];
    }
}
