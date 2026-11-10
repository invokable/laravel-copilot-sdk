<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A notice that remains visible until dismissed or replaced.
 *
 * @experimental
 */
readonly class LocalModelNotice implements Arrayable
{
    public function __construct(
        public string $modelId,
        public string $tone,
        public string $text,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            modelId: $data['modelId'] ?? '',
            tone: $data['tone'] ?? '',
            text: $data['text'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'modelId' => $this->modelId,
            'tone' => $this->tone,
            'text' => $this->text,
        ];
    }
}
