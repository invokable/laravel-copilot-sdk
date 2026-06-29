<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Snapshot of allowed directories and primary path.
 */
readonly class PermissionPathsList implements Arrayable
{
    /**
     * @param  list<string>  $directories
     */
    public function __construct(
        public array $directories,
        public string $primary,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            directories: Arr::array($data, 'directories', []),
            primary: Arr::string($data, 'primary', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'directories' => $this->directories,
            'primary' => $this->primary,
        ];
    }
}
