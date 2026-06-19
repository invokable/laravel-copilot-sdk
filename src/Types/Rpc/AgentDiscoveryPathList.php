<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Canonical locations where custom agents can be created so the runtime will recognize them.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class AgentDiscoveryPathList implements Arrayable
{
    /**
     * @param  AgentDiscoveryPath[]  $paths  Canonical agent create/discovery directories, in priority order
     */
    public function __construct(
        public array $paths,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            paths: array_map(
                fn (array $path) => AgentDiscoveryPath::fromArray($path),
                $data['paths'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'paths' => array_map(fn (AgentDiscoveryPath $p) => $p->toArray(), $this->paths),
        ];
    }
}
