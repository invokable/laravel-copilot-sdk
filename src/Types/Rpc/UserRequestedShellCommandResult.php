<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of executing a user-requested shell command.
 */
readonly class UserRequestedShellCommandResult implements Arrayable
{
    public function __construct(
        public string $toolCallId,
        public bool $success,
        public string $output,
        public ?int $exitCode = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            toolCallId: Arr::string($data, 'toolCallId'),
            success: Arr::boolean($data, 'success'),
            output: Arr::string($data, 'output'),
            exitCode: $data['exitCode'] ?? null,
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'toolCallId' => $this->toolCallId,
            'success' => $this->success,
            'output' => $this->output,
            'exitCode' => $this->exitCode,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
