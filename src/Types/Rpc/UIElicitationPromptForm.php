<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Support\Arr;

readonly class UIElicitationPromptForm implements UIElicitationPrompt
{
    /**
     * @param  array<string, mixed>|null  $_meta
     */
    public function __construct(
        public string $mode,
        public string $message,
        public UIElicitationSnapshotSchema $requestedSchema,
        public ?array $_meta = null,
        public ?McpTaskMetadata $task = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $meta = $data['_meta'] ?? null;
        $task = $data['task'] ?? null;

        return new self(
            mode: Arr::string($data, 'mode', 'form'),
            message: Arr::string($data, 'message', ''),
            requestedSchema: UIElicitationSnapshotSchema::fromArray(Arr::array($data, 'requestedSchema', [])),
            _meta: is_array($meta) ? $meta : null,
            task: is_array($task) ? McpTaskMetadata::fromArray($task) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode,
            'message' => $this->message,
            'requestedSchema' => $this->requestedSchema->toArray(),
            '_meta' => $this->_meta,
            'task' => $this->task?->toArray(),
        ], static fn ($value): bool => $value !== null);
    }
}
