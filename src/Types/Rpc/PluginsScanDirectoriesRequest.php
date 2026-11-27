<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class PluginsScanDirectoriesRequest implements Arrayable
{
    /**
     * @param  array<int, string>  $paths
     */
    public function __construct(
        public array $paths,
        public string $workingDirectory,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            paths: Arr::array($data, 'paths'),
            workingDirectory: Arr::string($data, 'workingDirectory'),
        );
    }

    public function toArray(): array
    {
        return [
            'paths' => $this->paths,
            'workingDirectory' => $this->workingDirectory,
        ];
    }
}
