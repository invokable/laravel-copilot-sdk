<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A message for a host's activity view.
 *
 * @experimental
 */
readonly class LocalModelTimelineMessage implements Arrayable
{
    public function __construct(
        public string $type,
        public string $text,
        public ?bool $markdown = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? '',
            text: $data['text'] ?? '',
            markdown: $data['markdown'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'text' => $this->text,
            ...array_filter([
                'markdown' => $this->markdown,
            ], static fn ($value) => $value !== null),
        ];
    }
}
