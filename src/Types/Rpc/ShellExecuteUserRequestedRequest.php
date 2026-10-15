<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for executing a user-requested shell command.
 */
readonly class ShellExecuteUserRequestedRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public string $command,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId'),
            command: Arr::string($data, 'command'),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'command' => $this->command,
        ];
    }
}
