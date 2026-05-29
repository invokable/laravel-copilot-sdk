<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Current lightweight tool metadata snapshot for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ToolsGetCurrentMetadataResult implements Arrayable
{
    /**
     * @param  CurrentToolMetadata[]|null  $tools  Current tool metadata, or null when tools have not been initialized yet.
     */
    public function __construct(
        public ?array $tools = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $tools = $data['tools'] ?? null;
        if (is_array($tools)) {
            $tools = array_map(fn (array $t) => CurrentToolMetadata::fromArray($t), $tools);
        }

        return new self(tools: $tools);
    }

    public function toArray(): array
    {
        return [
            'tools' => $this->tools !== null
                ? array_map(fn (CurrentToolMetadata $t) => $t->toArray(), $this->tools)
                : null,
        ];
    }
}
