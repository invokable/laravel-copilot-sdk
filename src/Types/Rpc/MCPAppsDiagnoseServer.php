<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * What the server returned for this session.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class MCPAppsDiagnoseServer implements Arrayable
{
    /**
     * @param  bool  $connected  Whether the named server is currently connected.
     * @param  array<string>  $sampleToolNames  Up to 5 tool names with `_meta.ui` for quick inspection.
     * @param  float  $toolCount  Total tools returned by the server's tools/list.
     * @param  float  $toolsWithUiMeta  Tools whose `_meta.ui` is populated (resourceUri and/or visibility set).
     */
    public function __construct(
        public bool $connected,
        public array $sampleToolNames,
        public float $toolCount,
        public float $toolsWithUiMeta,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            connected: $data['connected'],
            sampleToolNames: $data['sampleToolNames'],
            toolCount: (float) $data['toolCount'],
            toolsWithUiMeta: (float) $data['toolsWithUiMeta'],
        );
    }

    public function toArray(): array
    {
        return [
            'connected' => $this->connected,
            'sampleToolNames' => $this->sampleToolNames,
            'toolCount' => $this->toolCount,
            'toolsWithUiMeta' => $this->toolsWithUiMeta,
        ];
    }
}
