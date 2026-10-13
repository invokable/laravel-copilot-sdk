<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Provider response after opening a canvas.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CanvasProviderOpenResult implements Arrayable
{
    public function __construct(
        public ?string $url = null,
        public ?string $title = null,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? null,
            title: $data['title'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'title' => $this->title,
            'status' => $this->status,
        ], fn ($value) => $value !== null);
    }
}
