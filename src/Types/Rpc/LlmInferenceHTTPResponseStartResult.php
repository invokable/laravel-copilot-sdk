<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Whether the response start frame was accepted.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class LlmInferenceHTTPResponseStartResult implements Arrayable
{
    /**
     * @param  bool  $accepted  True when the response start was matched to a pending request; false when unknown.
     */
    public function __construct(
        public bool $accepted,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            accepted: Arr::boolean($data, 'accepted'),
        );
    }

    public function toArray(): array
    {
        return [
            'accepted' => $this->accepted,
        ];
    }
}
