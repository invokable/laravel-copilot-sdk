<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Upgrade guidance for an account without delegation access. */
readonly class SessionQuotaDelegateWarning implements Arrayable
{
    public function __construct(
        public string $text,
        public string $url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            url: $data['url'] ?? '',
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'url' => $this->url,
        ];
    }
}
