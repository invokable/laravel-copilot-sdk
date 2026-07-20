<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A single configuration entry in a {@see CopilotExpAssignmentResponse}. Each
 * entry carries an identifier and a bag of typed parameter values.
 */
readonly class ExpConfigEntry implements Arrayable
{
    /**
     * @param  string  $id  Identifier of the configuration entry.
     * @param  array<string, string|int|float|bool|null>  $parameters  Parameter values keyed by parameter name.
     */
    public function __construct(
        public string $id,
        public array $parameters = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'Id', ''),
            parameters: Arr::array($data, 'Parameters', []),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'Id' => $this->id,
            'Parameters' => $this->parameters,
        ], fn ($v) => $v !== null);
    }
}
