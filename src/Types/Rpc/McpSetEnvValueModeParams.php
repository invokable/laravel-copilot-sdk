<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Parameters for choosing how MCP environment values are resolved.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpSetEnvValueModeParams implements Arrayable
{
    /**
     * @param  'direct'|'indirect'  $mode
     */
    public function __construct(
        public string $mode,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: Arr::string($data, 'mode'),
        );
    }

    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
        ];
    }
}
