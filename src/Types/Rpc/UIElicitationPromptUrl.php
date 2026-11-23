<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Support\Arr;

readonly class UIElicitationPromptUrl implements UIElicitationPrompt
{
    /**
     * @param  array<string, mixed>|null  $_meta
     */
    public function __construct(
        public string $mode,
        public string $message,
        public string $url,
        public string $elicitationId,
        public ?array $_meta = null,
        public ?McpTaskMetadata $task = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $meta = $data['_meta'] ?? null;
        $task = $data['task'] ?? null;

        return new self(
            mode: Arr::string($data, 'mode', 'url'),
            message: Arr::string($data, 'message', ''),
            url: Arr::string($data, 'url', ''),
            elicitationId: Arr::string($data, 'elicitationId', ''),
            _meta: is_array($meta) ? $meta : null,
            task: is_array($task) ? McpTaskMetadata::fromArray($task) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode,
            'message' => $this->message,
            'url' => $this->url,
            'elicitationId' => $this->elicitationId,
            '_meta' => $this->_meta,
            'task' => $this->task?->toArray(),
        ], static fn ($value): bool => $value !== null);
    }
}
