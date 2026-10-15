<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for handling a pending user input request.
 */
readonly class UIHandlePendingUserInputRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public UIUserInputResponse|array $response,
    ) {}

    public static function fromArray(array $data): self
    {
        $response = $data['response'] ?? [];

        return new self(
            requestId: Arr::string($data, 'requestId'),
            response: $response instanceof UIUserInputResponse
                ? $response
                : UIUserInputResponse::fromArray(Arr::array(['response' => $response], 'response')),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'response' => $this->response instanceof UIUserInputResponse
                ? $this->response->toArray()
                : $this->response,
        ];
    }
}
