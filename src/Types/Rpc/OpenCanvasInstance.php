<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Open canvas instance snapshot.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class OpenCanvasInstance implements Arrayable
{
    /**
     * @param  string  $canvasId  Provider-local canvas identifier.
     * @param  string  $extensionId  Owning provider identifier.
     * @param  string  $instanceId  Stable caller-supplied canvas instance identifier.
     * @param  string|null  $extensionName  Owning extension display name, when available.
     * @param  mixed  $input  Input supplied when the instance was opened.
     * @param  string|null  $status  Provider-supplied status text.
     * @param  string|null  $title  Rendered title.
     * @param  string|null  $url  URL for web-rendered canvases.
     */
    public function __construct(
        public string $canvasId,
        public string $extensionId,
        public string $instanceId,
        public ?string $extensionName = null,
        public mixed $input = null,
        public ?string $status = null,
        public ?string $title = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            canvasId: $data['canvasId'],
            extensionId: $data['extensionId'],
            instanceId: $data['instanceId'],
            extensionName: $data['extensionName'] ?? null,
            input: $data['input'] ?? null,
            status: $data['status'] ?? null,
            title: $data['title'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'canvasId' => $this->canvasId,
            'extensionId' => $this->extensionId,
            'instanceId' => $this->instanceId,
            'extensionName' => $this->extensionName,
            'input' => $this->input,
            'status' => $this->status,
            'title' => $this->title,
            'url' => $this->url,
        ], fn ($value) => $value !== null);
    }
}
