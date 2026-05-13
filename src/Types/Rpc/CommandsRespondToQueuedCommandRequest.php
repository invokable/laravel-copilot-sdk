<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for responding to a queued command.
 */
readonly class CommandsRespondToQueuedCommandRequest implements Arrayable
{
    /**
     * @param  string  $requestId  Request ID from the queued command event
     * @param  QueuedCommandResult  $result  Result of the queued command execution
     */
    public function __construct(
        public string $requestId,
        public QueuedCommandResult $result,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['requestId'],
            result: QueuedCommandResult::fromArray($data['result'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'result' => $this->result->toArray(),
        ];
    }
}
