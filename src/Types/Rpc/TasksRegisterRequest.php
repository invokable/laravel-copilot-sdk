<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Request to register or reclaim a client-owned task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TasksRegisterRequest implements Arrayable
{
    public function __construct(
        public string $clientTaskId,
        public string $description,
        public bool $cancellable,
        public ?string $displayName = null,
        public ?int $expectedSequence = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            clientTaskId: Arr::string($data, 'clientTaskId', ''),
            description: Arr::string($data, 'description', ''),
            cancellable: Arr::boolean($data, 'cancellable', false),
            displayName: $data['displayName'] ?? null,
            expectedSequence: isset($data['expectedSequence']) ? (int) $data['expectedSequence'] : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'type' => 'client',
            'clientTaskId' => $this->clientTaskId,
            'description' => $this->description,
            'cancellable' => $this->cancellable,
        ];

        if ($this->displayName !== null) {
            $result['displayName'] = $this->displayName;
        }
        if ($this->expectedSequence !== null) {
            $result['expectedSequence'] = $this->expectedSequence;
        }

        return $result;
    }
}
