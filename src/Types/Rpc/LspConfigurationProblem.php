<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A project configuration that the tolerant loader could not use.
 *
 * @experimental
 */
readonly class LspConfigurationProblem implements Arrayable
{
    public function __construct(
        public string $path,
        public string $error,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            path: Arr::string($data, 'path', ''),
            error: Arr::string($data, 'error', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'error' => $this->error,
        ];
    }
}
