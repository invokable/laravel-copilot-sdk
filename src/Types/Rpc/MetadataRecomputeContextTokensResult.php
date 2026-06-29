<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Token totals after re-tokenizing session context.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class MetadataRecomputeContextTokensResult implements Arrayable
{
    public function __construct(
        public int $totalTokens,
        public int $messagesTokenCount,
        public int $systemTokenCount,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            totalTokens: Arr::integer($data, 'totalTokens', 0),
            messagesTokenCount: Arr::integer($data, 'messagesTokenCount', 0),
            systemTokenCount: Arr::integer($data, 'systemTokenCount', 0),
        );
    }

    public function toArray(): array
    {
        return [
            'totalTokens' => $this->totalTokens,
            'messagesTokenCount' => $this->messagesTokenCount,
            'systemTokenCount' => $this->systemTokenCount,
        ];
    }
}
