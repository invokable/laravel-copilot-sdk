<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of sending a message to an agent task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksSendMessageResult implements Arrayable
{
    /**
     * @param  bool  $sent  Whether the message was successfully delivered or steered
     * @param  string|null  $error  Error message if delivery failed
     */
    public function __construct(
        public bool $sent,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sent: Arr::boolean($data, 'sent', false),
            error: $data['error'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'sent' => $this->sent,
        ];

        if ($this->error !== null) {
            $result['error'] = $this->error;
        }

        return $result;
    }
}
