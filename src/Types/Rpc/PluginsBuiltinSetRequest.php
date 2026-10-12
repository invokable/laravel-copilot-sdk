<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Trusted built-in plugin directories to use for this runtime process.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
readonly class PluginsBuiltinSetRequest implements Arrayable
{
    /**
     * @param  array<string>  $paths
     */
    public function __construct(
        public array $paths,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(paths: $data['paths'] ?? []);
    }

    public function toArray(): array
    {
        return ['paths' => $this->paths];
    }
}
