<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Compaction summary checkpoint to persist.
 *
 * @experimental
 */
readonly class WorkspacesAddSummaryRequest implements Arrayable
{
    public function __construct(
        public string $title,
        public string $content,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: Arr::string($data, 'title'),
            content: Arr::string($data, 'content'),
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }
}
