<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Request to send a message to a running agent task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksSendMessageRequest implements Arrayable
{
    /**
     * @param  string  $id  Agent task identifier
     * @param  string  $message  Message content to send to the agent
     * @param  string|null  $fromAgentId  Agent ID of the sender, if sent on behalf of another agent
     */
    public function __construct(
        public string $id,
        public string $message,
        public ?string $fromAgentId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            message: $data['message'] ?? '',
            fromAgentId: $data['fromAgentId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'id' => $this->id,
            'message' => $this->message,
        ];

        if ($this->fromAgentId !== null) {
            $result['fromAgentId'] = $this->fromAgentId;
        }

        return $result;
    }
}
