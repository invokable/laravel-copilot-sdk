<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A secret a transport choice needs, referenced by placeholder.
 *
 * @experimental
 */
readonly class McpPlanSecretPlaceholder implements Arrayable
{
    public function __construct(
        public string $key,
        public string $placeholder,
        public ?string $title = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            key: $data['key'],
            placeholder: $data['placeholder'],
            title: $data['title'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'key' => $this->key,
            'placeholder' => $this->placeholder,
        ];
        if ($this->title !== null) {
            $arr['title'] = $this->title;
        }

        return $arr;
    }
}
