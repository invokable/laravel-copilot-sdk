<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for a process-wide, read-only language-server inventory.
 *
 * @experimental
 */
readonly class LspListRequest implements Arrayable
{
    public function __construct(
        public string $workingDirectory,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            workingDirectory: Arr::string($data, 'workingDirectory', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'workingDirectory' => $this->workingDirectory,
        ];
    }
}
