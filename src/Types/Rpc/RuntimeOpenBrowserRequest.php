<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * An authorization URL for the host browser.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class RuntimeOpenBrowserRequest implements Arrayable
{
    public function __construct(
        public string $url,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url: Arr::string($data, 'url'),
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
        ];
    }
}
