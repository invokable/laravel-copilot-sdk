<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\UIAutoModeSwitchResponse;

/**
 * Parameters for handling a pending auto mode switch request.
 */
readonly class UIHandlePendingAutoModeSwitchRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public UIAutoModeSwitchResponse|string $response,
    ) {}

    public static function fromArray(array $data): self
    {
        $response = Arr::string($data, 'response');

        return new self(
            requestId: Arr::string($data, 'requestId'),
            response: UIAutoModeSwitchResponse::from($response),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'response' => $this->response instanceof UIAutoModeSwitchResponse
                ? $this->response->value
                : $this->response,
        ];
    }
}
