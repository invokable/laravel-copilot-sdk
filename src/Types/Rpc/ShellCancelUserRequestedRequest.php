<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * User-requested shell execution cancellation handle.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ShellCancelUserRequestedRequest implements Arrayable
{
    /**
     * @param  string  $requestId  Request ID previously passed to executeUserRequested.
     */
    public function __construct(
        public string $requestId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestId: $data['requestId'],
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
        ];
    }
}
