<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Published token limits of a local model.
 *
 * @experimental
 */
readonly class LocalModelLimits implements Arrayable
{
    public function __construct(
        public int $maxPromptTokens,
        public int $maxContextWindowTokens,
        public int $maxOutputTokens,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            maxPromptTokens: (int) ($data['maxPromptTokens'] ?? 0),
            maxContextWindowTokens: (int) ($data['maxContextWindowTokens'] ?? 0),
            maxOutputTokens: (int) ($data['maxOutputTokens'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'maxPromptTokens' => $this->maxPromptTokens,
            'maxContextWindowTokens' => $this->maxContextWindowTokens,
            'maxOutputTokens' => $this->maxOutputTokens,
        ];
    }
}
