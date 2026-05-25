<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\CanvasInstanceAvailability;

/**
 * Open canvas instance snapshot.
 *
 * Experimental: this type is part of an experimental API and may change or be removed.
 */
readonly class OpenCanvasInstance implements Arrayable
{
    /**
     * @param  CanvasInstanceAvailability|string  $availability  Runtime-controlled routing state.
     * @param  string  $canvasId  Provider-local canvas identifier.
     * @param  string  $extensionId  Owning provider identifier.
     * @param  string  $instanceId  Stable caller-supplied canvas instance identifier.
     * @param  bool  $reopen  Whether this snapshot came from an idempotent reopen.
     * @param  string|null  $extensionName  Owning extension display name, when available.
     * @param  mixed  $input  Input supplied when the instance was opened.
     * @param  string|null  $status  Provider-supplied status text.
     * @param  string|null  $title  Rendered title.
     * @param  string|null  $url  URL for web-rendered canvases.
     */
    public function __construct(
        public CanvasInstanceAvailability|string $availability,
        public string $canvasId,
        public string $extensionId,
        public string $instanceId,
        public bool $reopen,
        public ?string $extensionName = null,
        public mixed $input = null,
        public ?string $status = null,
        public ?string $title = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $availability = CanvasInstanceAvailability::tryFrom($data['availability']) ?? $data['availability'];

        return new self(
            availability: $availability,
            canvasId: $data['canvasId'],
            extensionId: $data['extensionId'],
            instanceId: $data['instanceId'],
            reopen: $data['reopen'],
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
            'availability' => $this->availability instanceof CanvasInstanceAvailability ? $this->availability->value : $this->availability,
            'canvasId' => $this->canvasId,
            'extensionId' => $this->extensionId,
            'instanceId' => $this->instanceId,
            'reopen' => $this->reopen,
            'extensionName' => $this->extensionName,
            'input' => $this->input,
            'status' => $this->status,
            'title' => $this->title,
            'url' => $this->url,
        ], fn ($value) => $value !== null);
    }
}
