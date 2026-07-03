<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A single large message currently in context.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ContextHeaviestMessage implements Arrayable
{
    /**
     * @param  string  $id  Stable identifier for this message within the snapshot.
     * @param  string  $label  Human-readable source label, e.g. `tool: bash` or `skill: tmux`. Presentation-only.
     * @param  string  $role  Role of the chat message (`user`, `assistant`, or `tool`).
     * @param  int  $tokens  Token count currently in context for this individual message.
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $role,
        public int $tokens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id', ''),
            label: Arr::string($data, 'label', ''),
            role: Arr::string($data, 'role', ''),
            tokens: Arr::integer($data, 'tokens', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'role' => $this->role,
            'tokens' => $this->tokens,
        ];
    }
}
