<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\TaskClientOwnerKind;
use Revolution\Copilot\Enums\TaskClientOwnerPresence;

/**
 * Public owner attribution for a client-owned task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskClientOwner implements Arrayable
{
    public function __construct(
        public string $participantId,
        public string $joinId,
        public TaskClientOwnerKind $kind,
        public TaskClientOwnerPresence $presence,
        public ?string $displayName = null,
        public ?string $source = null,
        public ?string $disconnectedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            participantId: Arr::string($data, 'participantId', ''),
            joinId: Arr::string($data, 'joinId', ''),
            kind: TaskClientOwnerKind::from($data['kind'] ?? 'sdk'),
            presence: TaskClientOwnerPresence::from($data['presence'] ?? 'connected'),
            displayName: $data['displayName'] ?? null,
            source: $data['source'] ?? null,
            disconnectedAt: $data['disconnectedAt'] ?? null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'participantId' => $this->participantId,
            'joinId' => $this->joinId,
            'kind' => $this->kind->value,
        ];

        if ($this->displayName !== null) {
            $result['displayName'] = $this->displayName;
        }
        if ($this->source !== null) {
            $result['source'] = $this->source;
        }
        $result['presence'] = $this->presence->value;
        if ($this->disconnectedAt !== null) {
            $result['disconnectedAt'] = $this->disconnectedAt;
        }

        return $result;
    }
}
