<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

readonly class UIPluginActivationContext implements Arrayable
{
    public function __construct(
        public string $requestedWorkingDirectory,
        public string $resolvedWorkingDirectory,
        public string $settingsRoot,
        public bool $trusted,
        public string $trustSource,
        public ?string $gitRoot = null,
        public ?string $trustRoot = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            requestedWorkingDirectory: Arr::string($data, 'requestedWorkingDirectory', ''),
            resolvedWorkingDirectory: Arr::string($data, 'resolvedWorkingDirectory', ''),
            settingsRoot: Arr::string($data, 'settingsRoot', ''),
            gitRoot: isset($data['gitRoot']) ? Arr::string($data, 'gitRoot') : null,
            trustRoot: isset($data['trustRoot']) ? Arr::string($data, 'trustRoot') : null,
            trusted: Arr::boolean($data, 'trusted', false),
            trustSource: Arr::string($data, 'trustSource', ''),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestedWorkingDirectory' => $this->requestedWorkingDirectory,
            'resolvedWorkingDirectory' => $this->resolvedWorkingDirectory,
            'settingsRoot' => $this->settingsRoot,
            'gitRoot' => $this->gitRoot,
            'trustRoot' => $this->trustRoot,
            'trusted' => $this->trusted,
            'trustSource' => $this->trustSource,
        ], static fn ($value): bool => $value !== null);
    }
}
