<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Information about a slash command available in the session.
 */
readonly class SlashCommandInfo implements Arrayable
{
    /**
     * @param  bool  $allowDuringAgentExecution  Whether the command may run while an agent turn is active.
     * @param  string  $description  Human-readable command description.
     * @param  string  $kind  Coarse command category (builtin, skill, client).
     * @param  string  $name  Canonical command name without a leading slash.
     * @param  ?array  $aliases  Canonical aliases without leading slashes.
     * @param  ?bool  $experimental  Whether the command is experimental.
     * @param  SlashCommandInput|null  $input  Optional unstructured input hint.
     */
    public function __construct(
        public bool $allowDuringAgentExecution,
        public string $description,
        public string $kind,
        public string $name,
        public ?array $aliases = null,
        public ?bool $experimental = null,
        public ?SlashCommandInput $input = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            allowDuringAgentExecution: $data['allowDuringAgentExecution'] ?? false,
            description: $data['description'] ?? '',
            kind: $data['kind'] ?? '',
            name: $data['name'] ?? '',
            aliases: $data['aliases'] ?? null,
            experimental: $data['experimental'] ?? null,
            input: isset($data['input']) ? SlashCommandInput::fromArray($data['input']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'allowDuringAgentExecution' => $this->allowDuringAgentExecution,
            'description' => $this->description,
            'kind' => $this->kind,
            'name' => $this->name,
            'aliases' => $this->aliases,
            'experimental' => $this->experimental,
            'input' => $this->input?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
