<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPluginTrustContext implements Arrayable
{
    public function __construct(
        public string $mode,
        public ?bool $trusted = null,
        public ?string $source = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: Arr::string($data, 'mode', ''),
            trusted: isset($data['trusted']) ? Arr::boolean($data, 'trusted') : null,
            source: isset($data['source']) ? Arr::string($data, 'source') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode,
            'trusted' => $this->trusted,
            'source' => $this->source,
        ], static fn ($value): bool => $value !== null);
    }
}
