<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Progress snapshot for a shell task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskShellProgress implements Arrayable
{
    public function __construct(
        public string $recentOutput,
        public ?int $pid = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            recentOutput: Arr::string($data, 'recentOutput', ''),
            pid: isset($data['pid']) ? (int) $data['pid'] : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'shell',
            'recentOutput' => $this->recentOutput,
        ];

        if ($this->pid !== null) {
            $result['pid'] = $this->pid;
        }

        return $result;
    }
}
