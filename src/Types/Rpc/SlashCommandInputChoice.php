<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * A literal choice the command input accepts, with a human-facing description.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SlashCommandInputChoice implements Arrayable
{
    /**
     * @param  string  $name  The literal choice value (e.g. 'on', 'off', 'show').
     * @param  string  $description  Human-readable description shown alongside the choice.
     */
    public function __construct(
        public string $name,
        public string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: Arr::string($data, 'name', ''),
            description: Arr::string($data, 'description', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
