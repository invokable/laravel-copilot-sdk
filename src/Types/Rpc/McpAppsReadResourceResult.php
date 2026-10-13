<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Resource contents returned by the MCP server.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class McpAppsReadResourceResult implements Arrayable
{
    /**
     * @param  array<McpAppsResourceContent>  $contents  Resource contents returned by the server.
     */
    public function __construct(
        public array $contents,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            contents: array_map(
                fn (array $content) => McpAppsResourceContent::fromArray($content),
                Arr::array($data, 'contents'),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'contents' => array_map(
                fn (McpAppsResourceContent $content) => $content->toArray(),
                $this->contents,
            ),
        ];
    }
}
