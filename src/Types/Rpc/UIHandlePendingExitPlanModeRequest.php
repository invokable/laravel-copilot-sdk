<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for handling a pending exit plan mode request.
 */
readonly class UIHandlePendingExitPlanModeRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public UIExitPlanModeResponse|array $response,
    ) {}

    public static function fromArray(array $data): self
    {
        $response = $data['response'] ?? [];

        return new self(
            requestId: Arr::string($data, 'requestId'),
            response: $response instanceof UIExitPlanModeResponse
                ? $response
                : UIExitPlanModeResponse::fromArray(Arr::array(['response' => $response], 'response')),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'response' => $this->response instanceof UIExitPlanModeResponse
                ? $this->response->toArray()
                : $this->response,
        ];
    }
}
