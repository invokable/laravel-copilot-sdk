<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Post-compaction context window usage breakdown.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ContextWindow implements Arrayable
{
    /**
     * @param  int  $tokenLimit  Maximum token count for the model's context window
     * @param  int  $currentTokens  Current total tokens in the context window (system + conversation + tool definitions)
     * @param  int  $messagesLength  Current number of messages in the conversation
     * @param  ?int  $systemTokens  Token count from system message(s)
     * @param  ?int  $conversationTokens  Token count from non-system messages (user, assistant, tool)
     * @param  ?int  $toolDefinitionsTokens  Token count from tool definitions
     */
    public function __construct(
        public int $tokenLimit,
        public int $currentTokens,
        public int $messagesLength,
        public ?int $systemTokens = null,
        public ?int $conversationTokens = null,
        public ?int $toolDefinitionsTokens = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tokenLimit: (int) $data['tokenLimit'],
            currentTokens: (int) $data['currentTokens'],
            messagesLength: (int) $data['messagesLength'],
            systemTokens: isset($data['systemTokens']) ? (int) $data['systemTokens'] : null,
            conversationTokens: isset($data['conversationTokens']) ? (int) $data['conversationTokens'] : null,
            toolDefinitionsTokens: isset($data['toolDefinitionsTokens']) ? (int) $data['toolDefinitionsTokens'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'tokenLimit' => $this->tokenLimit,
            'currentTokens' => $this->currentTokens,
            'messagesLength' => $this->messagesLength,
            'systemTokens' => $this->systemTokens,
            'conversationTokens' => $this->conversationTokens,
            'toolDefinitionsTokens' => $this->toolDefinitionsTokens,
        ], fn ($v) => $v !== null);
    }
}
