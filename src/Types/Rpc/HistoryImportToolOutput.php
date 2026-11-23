<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class HistoryImportToolOutput implements Arrayable
{
    public function __construct(public string $content) {}

    public static function fromArray(array $data): self
    {
        return new self(content: Arr::string($data, 'content'));
    }

    public function toArray(): array
    {
        return ['content' => $this->content];
    }
}
