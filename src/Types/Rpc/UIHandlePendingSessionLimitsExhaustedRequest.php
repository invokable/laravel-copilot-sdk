<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for resolving a pending session_limits_exhausted.requested event.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class UIHandlePendingSessionLimitsExhaustedRequest implements Arrayable
{
    /**
     * @param  string  $requestId  The unique request ID from the session_limits_exhausted.requested event
     * @param  UISessionLimitsExhaustedResponse|array  $response  The user's selected limit action
     */
    public function __construct(
        public string $requestId,
        public UISessionLimitsExhaustedResponse|array $response,
    ) {}

    public static function fromArray(array $data): self
    {
        $response = isset($data['response'])
            ? ($data['response'] instanceof UISessionLimitsExhaustedResponse
                ? $data['response']
                : UISessionLimitsExhaustedResponse::fromArray($data['response']))
            : UISessionLimitsExhaustedResponse::fromArray([]);

        return new self(
            requestId: Arr::string($data, 'requestId'),
            response: $response,
        );
    }

    public function toArray(): array
    {
        $response = $this->response instanceof UISessionLimitsExhaustedResponse
            ? $this->response->toArray()
            : $this->response;

        return [
            'requestId' => $this->requestId,
            'response' => $response,
        ];
    }
}
