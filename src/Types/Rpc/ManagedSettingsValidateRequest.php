<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** A candidate managed-settings document to validate without applying it. */
readonly class ManagedSettingsValidateRequest implements Arrayable
{
    public function __construct(
        public mixed $content,
        public ?string $layer = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['content'] ?? null,
            layer: $data['layer'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'content' => $this->content,
            'layer' => $this->layer,
        ], fn ($value) => $value !== null);
    }
}
