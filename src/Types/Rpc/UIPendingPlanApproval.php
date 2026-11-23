<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPendingPlanApproval implements Arrayable
{
    public function __construct(
        public string $requestId,
        public UIPlanApprovalPrompt $request,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: Arr::string($data, 'requestId', ''),
            request: UIPlanApprovalPrompt::fromArray(Arr::array($data, 'request', [])),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'request' => $this->request->toArray(),
        ];
    }
}
