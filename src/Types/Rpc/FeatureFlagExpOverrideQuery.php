<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Experiment value query with its static feature fallback.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class FeatureFlagExpOverrideQuery implements Arrayable
{
    public function __construct(
        public string $flag,
        public string $expFlag,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            flag: Arr::string($data, 'flag'),
            expFlag: Arr::string($data, 'expFlag'),
        );
    }

    public function toArray(): array
    {
        return [
            'flag' => $this->flag,
            'expFlag' => $this->expFlag,
        ];
    }
}
