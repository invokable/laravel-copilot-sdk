<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Parameters for handling a pending elicitation request.
 *
 * Used to respond to an `elicitation.requested` broadcast event
 * via the `session.ui.handlePendingElicitation` RPC method.
 */
readonly class UIHandlePendingElicitationRequest implements Arrayable
{
    /**
     * @param  string  $requestId  The unique request ID from the elicitation.requested event
     * @param  array{action: string, content?: array}  $result  The elicitation response (accept with form values, decline, or cancel)
     */
    public function __construct(
        public string $requestId,
        public array $result,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['requestId'],
            result: $data['result'],
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'result' => $this->result,
        ];
    }
}
