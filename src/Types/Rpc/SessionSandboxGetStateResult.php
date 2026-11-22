<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SandboxConfigSource;

/** @experimental */
readonly class SessionSandboxGetStateResult implements Arrayable
{
    public function __construct(
        public int $incarnation,
        public int $revision,
        public mixed $config,
        public SandboxConfigSource|string $configSource,
        public bool $disabledForSession,
        public bool $autoApproveBypass,
        public ?bool $sessionOverride,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = Arr::string($data, 'configSource');

        return new self(
            incarnation: Arr::integer($data, 'incarnation'),
            revision: Arr::integer($data, 'revision'),
            config: $data['config'] ?? null,
            configSource: SandboxConfigSource::tryFrom($source) ?? $source,
            disabledForSession: Arr::boolean($data, 'disabledForSession'),
            autoApproveBypass: Arr::boolean($data, 'autoApproveBypass'),
            sessionOverride: isset($data['sessionOverride']) ? Arr::boolean($data, 'sessionOverride') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'incarnation' => $this->incarnation,
            'revision' => $this->revision,
            'config' => $this->config,
            'configSource' => $this->configSource instanceof SandboxConfigSource ? $this->configSource->value : $this->configSource,
            'disabledForSession' => $this->disabledForSession,
            'autoApproveBypass' => $this->autoApproveBypass,
            'sessionOverride' => $this->sessionOverride,
        ];
    }
}
