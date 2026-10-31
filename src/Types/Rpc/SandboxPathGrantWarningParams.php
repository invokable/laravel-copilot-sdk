<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * The path a user is about to grant to the sandbox.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SandboxPathGrantWarningParams implements Arrayable
{
    public function __construct(
        public string $resolvedPath,
        public ?bool $readOnly = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            resolvedPath: Arr::string($data, 'resolvedPath'),
            readOnly: isset($data['readOnly']) ? Arr::boolean($data, 'readOnly') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'resolvedPath' => $this->resolvedPath,
            'readOnly' => $this->readOnly,
        ], fn ($value) => $value !== null);
    }
}
