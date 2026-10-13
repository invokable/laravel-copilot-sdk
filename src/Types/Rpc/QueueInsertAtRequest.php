<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for inserting a queued message at a visible queue position.
 */
readonly class QueueInsertAtRequest implements Arrayable
{
    /**
     * @param  int  $position  Zero-based position in the visible queue.
     * @param  QueueInsertMessage  $message  Queued message contents and delivery metadata.
     */
    public function __construct(
        public int $position,
        public QueueInsertMessage $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            position: Arr::integer($data, 'position'),
            message: QueueInsertMessage::fromArray(Arr::array($data, 'message', [])),
        );
    }

    public function toArray(): array
    {
        return [
            'position' => $this->position,
            'message' => $this->message->toArray(),
        ];
    }
}
