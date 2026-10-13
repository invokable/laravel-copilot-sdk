<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Types\Rpc\CanvasAction;

/**
 * Declarative metadata for a canvas contributed by a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class CanvasDeclaration implements Arrayable
{
    /**
     * @param  CanvasAction[]|array[]|null  $actions
     */
    public function __construct(
        public string $id,
        public string $displayName,
        public string $description,
        public mixed $inputSchema = null,
        public ?array $actions = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
            displayName: Arr::string($data, 'displayName'),
            description: Arr::string($data, 'description'),
            inputSchema: $data['inputSchema'] ?? null,
            actions: isset($data['actions'])
                ? array_map(
                    fn (CanvasAction|array $action) => $action instanceof CanvasAction
                        ? $action
                        : CanvasAction::fromArray($action),
                    $data['actions'],
                )
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'displayName' => $this->displayName,
            'description' => $this->description,
            'inputSchema' => $this->inputSchema,
            'actions' => $this->actions === null
                ? null
                : array_map(
                    fn (CanvasAction|array $action) => $action instanceof CanvasAction
                        ? $action->toArray()
                        : CanvasAction::fromArray($action)->toArray(),
                    $this->actions,
                ),
        ], fn ($value) => $value !== null);
    }
}
