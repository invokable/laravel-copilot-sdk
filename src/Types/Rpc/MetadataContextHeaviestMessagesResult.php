<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The heaviest individual messages in the session's context window, most-expensive first.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataContextHeaviestMessagesResult implements Arrayable
{
    /**
     * @param  int  $totalTokens  Total token count of the current context window.
     * @param  ContextHeaviestMessage[]  $messages  Heaviest messages, most-expensive first.
     */
    public function __construct(
        public int $totalTokens,
        public array $messages,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalTokens: Arr::integer($data, 'totalTokens', 0),
            messages: array_map(
                fn (array $msg) => ContextHeaviestMessage::fromArray($msg),
                $data['messages'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'totalTokens' => $this->totalTokens,
            'messages' => array_map(fn ($m) => $m->toArray(), $this->messages),
        ];
    }
}
