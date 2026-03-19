<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for starting fleet mode.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionFleetStartParams implements Arrayable
{
    /**
     * @param  ?string  $prompt  Optional user prompt to combine with fleet instructions
     */
    public function __construct(
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
