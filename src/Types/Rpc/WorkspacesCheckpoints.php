<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Workspace checkpoint metadata.
 *
 * @experimental
 */
readonly class WorkspacesCheckpoints implements Arrayable
{
    public function __construct(
        public string $filename,
        public int $number,
        public string $title,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            filename: Arr::string($data, 'filename'),
            number: Arr::integer($data, 'number'),
            title: Arr::string($data, 'title'),
        );
    }

    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'number' => $this->number,
            'title' => $this->title,
        ];
    }
}
