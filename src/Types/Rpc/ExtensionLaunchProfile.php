<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Host-provided command for launching an extension entrypoint.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionLaunchProfile implements Arrayable
{
    /**
     * @param  list<string>  $args
     * @param  array<string, string>  $env
     */
    public function __construct(
        public string $executable,
        public array $args = [],
        public array $env = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            executable: Arr::string($data, 'executable'),
            args: $data['args'] ?? [],
            env: $data['env'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'executable' => $this->executable,
            'args' => $this->args,
            'env' => $this->env,
        ];
    }
}
