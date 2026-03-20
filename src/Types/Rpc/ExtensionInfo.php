<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ExtensionSource;
use Revolution\Copilot\Enums\ExtensionStatus;

/**
 * Information about an extension.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class ExtensionInfo implements Arrayable
{
    /**
     * @param  string  $id  Source-qualified ID (e.g., 'project:my-ext', 'user:auth-helper')
     * @param  string  $name  Extension name (directory name)
     * @param  ExtensionSource  $source  Discovery source: project or user
     * @param  ExtensionStatus  $status  Current status: running, disabled, failed, or starting
     * @param  ?int  $pid  Process ID if the extension is running
     */
    public function __construct(
        public string $id,
        public string $name,
        public ExtensionSource $source,
        public ExtensionStatus $status,
        public ?int $pid = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            source: ExtensionSource::from($data['source']),
            status: ExtensionStatus::from($data['status']),
            pid: $data['pid'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'source' => $this->source->value,
            'status' => $this->status->value,
            'pid' => $this->pid,
        ], fn ($v) => $v !== null);
    }
}
