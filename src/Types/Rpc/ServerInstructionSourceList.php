<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Instruction sources discovered across user, repository, and plugin sources.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ServerInstructionSourceList implements Arrayable
{
    /**
     * @param  InstructionSource[]  $sources  All discovered instruction sources.
     */
    public function __construct(
        public array $sources,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sources: array_map(
                fn (array $source) => InstructionSource::fromArray($source),
                $data['sources'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'sources' => array_map(fn (InstructionSource $s) => $s->toArray(), $this->sources),
        ];
    }
}
