<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of getting instruction sources for a session.
 */
readonly class InstructionsGetSourcesResult implements Arrayable
{
    /**
     * @param  array<InstructionsSources>  $sources  Instruction sources for the session
     */
    public function __construct(
        public array $sources,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sources: array_map(
                fn (array $source) => InstructionsSources::fromArray($source),
                $data['sources'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'sources' => array_map(
                fn (InstructionsSources $source) => $source->toArray(),
                $this->sources,
            ),
        ];
    }
}
