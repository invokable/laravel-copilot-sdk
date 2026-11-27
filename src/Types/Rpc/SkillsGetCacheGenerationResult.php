<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * @experimental
 */
readonly class SkillsGetCacheGenerationResult implements Arrayable
{
    public function __construct(public int $generation) {}

    public static function fromArray(array $data): self
    {
        return new self(generation: Arr::integer($data, 'generation'));
    }

    public function toArray(): array
    {
        return ['generation' => $this->generation];
    }
}
