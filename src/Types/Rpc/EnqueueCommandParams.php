<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for queueing a slash command for FIFO processing.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class EnqueueCommandParams implements Arrayable
{
    /**
     * @param  string  $command  Slash-prefixed command string to enqueue.
     * @param  ?string  $displayText  Optional user-facing text for the queue row.
     */
    public function __construct(
        public string $command,
        public ?string $displayText = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            command: Arr::string($data, 'command', ''),
            displayText: isset($data['displayText']) ? Arr::string($data, 'displayText') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'command' => $this->command,
            'displayText' => $this->displayText,
        ], fn ($value) => $value !== null);
    }
}
