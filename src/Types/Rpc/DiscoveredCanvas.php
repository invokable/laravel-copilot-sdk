<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Canvas available in the current session.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class DiscoveredCanvas implements Arrayable
{
    /**
     * @param  string  $canvasId  Provider-local canvas identifier.
     * @param  string  $description  Short, single-sentence description shown to the agent in canvas catalogs.
     * @param  string  $displayName  Human-readable canvas name.
     * @param  string  $extensionId  Owning provider identifier.
     * @param  CanvasAction[]|null  $actions  Actions the agent or host may invoke on an open instance.
     * @param  string|null  $extensionName  Owning extension display name, when available.
     * @param  string|null  $icon  Host-local PNG path for the canvas icon, when supplied.
     * @param  mixed  $inputSchema  JSON Schema for canvas open input.
     */
    public function __construct(
        public string $canvasId,
        public string $description,
        public string $displayName,
        public string $extensionId,
        public ?array $actions = null,
        public ?string $extensionName = null,
        public ?string $icon = null,
        public mixed $inputSchema = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            canvasId: Arr::string($data, 'canvasId'),
            description: Arr::string($data, 'description'),
            displayName: Arr::string($data, 'displayName'),
            extensionId: Arr::string($data, 'extensionId'),
            actions: isset($data['actions']) ? array_map(fn ($a) => CanvasAction::fromArray($a), $data['actions']) : null,
            extensionName: $data['extensionName'] ?? null,
            icon: $data['icon'] ?? null,
            inputSchema: $data['inputSchema'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'canvasId' => $this->canvasId,
            'description' => $this->description,
            'displayName' => $this->displayName,
            'extensionId' => $this->extensionId,
            'actions' => $this->actions !== null ? array_map(fn ($a) => $a instanceof CanvasAction ? $a->toArray() : $a, $this->actions) : null,
            'extensionName' => $this->extensionName,
            'icon' => $this->icon,
            'inputSchema' => $this->inputSchema,
        ], fn ($value) => $value !== null);
    }
}
