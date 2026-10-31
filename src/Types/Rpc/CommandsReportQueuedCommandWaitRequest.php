<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\QueuedCommandWaitRequirement;

/**
 * Host report that its execution of a queued command started or stopped waiting.
 *
 * @internal
 */
readonly class CommandsReportQueuedCommandWaitRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public ?string $accountIdentity,
        public QueuedCommandWaitRequirement|string $requirement,
        public bool $waiting,
    ) {}

    public static function fromArray(array $data): self
    {
        $requirement = Arr::string($data, 'requirement', '');

        return new self(
            requestId: Arr::string($data, 'requestId', ''),
            accountIdentity: is_string($data['accountIdentity'] ?? null) ? $data['accountIdentity'] : null,
            requirement: QueuedCommandWaitRequirement::tryFrom($requirement) ?? $requirement,
            waiting: Arr::boolean($data, 'waiting', false),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'accountIdentity' => $this->accountIdentity,
            'requirement' => $this->requirement instanceof QueuedCommandWaitRequirement
                ? $this->requirement->value
                : $this->requirement,
            'waiting' => $this->waiting,
        ];
    }
}
