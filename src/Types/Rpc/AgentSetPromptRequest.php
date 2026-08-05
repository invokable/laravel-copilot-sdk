<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * In-memory authored prompt override for an available agent.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentSetPromptRequest implements Arrayable
{
    public function __construct(
        public string $id,
        public string $prompt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
            prompt: Arr::string($data, 'prompt'),
        );
    }

    public function toArray(): array
    {
        return ['id' => $this->id, 'prompt' => $this->prompt];
    }
}
