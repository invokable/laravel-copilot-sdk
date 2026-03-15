<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of a shell execution request.
 */
readonly class SessionShellExecResult implements Arrayable
{
    /**
     * @param  string  $processId  Unique identifier for tracking streamed output
     */
    public function __construct(
        public string $processId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            processId: $data['processId'],
        );
    }

    public function toArray(): array
    {
        return [
            'processId' => $this->processId,
        ];
    }
}
