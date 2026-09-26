<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Filesystem rules merged into the base sandbox policy.
 *
 * @experimental
 */
readonly class SandboxConfigUserPolicyFilesystem implements Arrayable
{
    public function __construct(
        public ?array $readwritePaths = null,
        public ?array $readonlyPaths = null,
        public ?array $deniedPaths = null,
        public ?bool $clearPolicyOnExit = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            readwritePaths: $data['readwritePaths'] ?? null,
            readonlyPaths: $data['readonlyPaths'] ?? null,
            deniedPaths: $data['deniedPaths'] ?? null,
            clearPolicyOnExit: $data['clearPolicyOnExit'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'readwritePaths' => $this->readwritePaths,
            'readonlyPaths' => $this->readonlyPaths,
            'deniedPaths' => $this->deniedPaths,
            'clearPolicyOnExit' => $this->clearPolicyOnExit,
        ], fn ($value) => $value !== null);
    }
}
