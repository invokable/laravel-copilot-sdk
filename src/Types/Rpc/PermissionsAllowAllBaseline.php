<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Original launch permission settings, before runtime escalation.
 *
 * @experimental
 */
readonly class PermissionsAllowAllBaseline implements Arrayable
{
    public function __construct(
        public bool $tools,
        public bool $paths,
        public bool $urls,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tools: Arr::boolean($data, 'tools'),
            paths: Arr::boolean($data, 'paths'),
            urls: Arr::boolean($data, 'urls'),
        );
    }

    public function toArray(): array
    {
        return [
            'tools' => $this->tools,
            'paths' => $this->paths,
            'urls' => $this->urls,
        ];
    }
}
