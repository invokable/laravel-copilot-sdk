<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for starting fleet mode.
 */
readonly class SessionFleetStartParams implements Arrayable
{
    public function __construct(
        /** Optional user prompt to combine with fleet instructions */
        public ?string $prompt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            prompt: $data['prompt'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'prompt' => $this->prompt,
        ], fn ($v) => $v !== null);
    }
}
