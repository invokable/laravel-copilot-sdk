<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Output read from a detached shell task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksReadOutputResult implements Arrayable
{
    public function __construct(
        public string $text,
        public string $cursor,
        public ?TaskShellOutputExit $exit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: Arr::string($data, 'text', ''),
            cursor: Arr::string($data, 'cursor', ''),
            exit: isset($data['exit']) ? TaskShellOutputExit::fromArray($data['exit']) : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'text' => $this->text,
            'cursor' => $this->cursor,
        ];

        if ($this->exit !== null) {
            $result['exit'] = $this->exit->toArray();
        }

        return $result;
    }
}
