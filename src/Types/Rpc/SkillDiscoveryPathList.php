<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Canonical locations where skills can be created so the runtime will recognize them.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SkillDiscoveryPathList implements Arrayable
{
    /**
     * @param  SkillDiscoveryPath[]  $paths  Canonical skill create/discovery directories, in priority order
     */
    public function __construct(
        public array $paths,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            paths: array_map(
                fn (array $path) => SkillDiscoveryPath::fromArray($path),
                $data['paths'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'paths' => array_map(fn (SkillDiscoveryPath $p) => $p->toArray(), $this->paths),
        ];
    }
}
