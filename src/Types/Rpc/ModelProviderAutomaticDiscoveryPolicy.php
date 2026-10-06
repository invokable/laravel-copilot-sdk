<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelProviderAutomaticDiscoveryPolicy implements Arrayable
{
    public function __construct(
        public string $mode,
        public string $networkScope,
        public bool $requiresInput = false,
        public bool $requiresTrust = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            mode: $data['mode'] ?? '',
            networkScope: $data['networkScope'] ?? '',
            requiresInput: (bool) ($data['requiresInput'] ?? false),
            requiresTrust: (bool) ($data['requiresTrust'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'networkScope' => $this->networkScope,
            'requiresInput' => $this->requiresInput,
            'requiresTrust' => $this->requiresTrust,
        ];
    }
}
