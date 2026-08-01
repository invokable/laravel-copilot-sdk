<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Action selected by the native deferred idle drain.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class QueueFinishDeferredIdleDrainResult implements Arrayable
{
    /**
     * @param  string  $action  One of `none`, `processQueue`, or `emitSessionIdle`.
     * @param  bool  $aborted  Whether the deferred idle was caused by an aborted foreground turn.
     */
    public function __construct(
        public string $action,
        public bool $aborted,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            action: Arr::string($data, 'action', ''),
            aborted: Arr::boolean($data, 'aborted', false),
        );
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'aborted' => $this->aborted,
        ];
    }
}
