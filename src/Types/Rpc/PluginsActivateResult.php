<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\PluginActivationStatus;

/**
 * @experimental
 */
readonly class PluginsActivateResult implements Arrayable
{
    public function __construct(
        public PluginActivationStatus $status,
        public ?string $message = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: PluginActivationStatus::from(Arr::string($data, 'status')),
            message: $data['message'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status->value,
            'message' => $this->message,
        ], fn ($value) => $value !== null);
    }
}
