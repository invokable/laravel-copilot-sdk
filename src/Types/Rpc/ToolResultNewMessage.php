<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A message injected by a tool result.
 *
 * @experimental
 */
readonly class ToolResultNewMessage implements Arrayable
{
    public function __construct(
        public string $content,
        public ?string $source = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: Arr::string($data, 'content'),
            source: $data['source'] ?? null,
        );
    }

    public function toArray(): array
    {
        $data = ['content' => $this->content];

        if ($this->source !== null) {
            $data['source'] = $this->source;
        }

        return $data;
    }
}
