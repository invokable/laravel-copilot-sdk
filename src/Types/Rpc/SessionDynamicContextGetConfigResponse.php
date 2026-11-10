<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Result of retrieving the dynamic-context configuration for a session.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionDynamicContextGetConfigResponse implements Arrayable
{
    public function __construct(
        public ?SessionDynamicContextConfig $config,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            config: isset($data['config']) ? SessionDynamicContextConfig::fromArray($data['config']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'config' => $this->config?->toArray(),
        ];
    }
}
