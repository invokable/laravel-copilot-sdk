<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SandboxProxyCaDeclineParams implements Arrayable
{
    /**
     * @param  array<string, string>  $environment
     */
    public function __construct(
        public string $configDir,
        public string $homeDirectory,
        public array $environment,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            configDir: Arr::string($data, 'configDir', ''),
            homeDirectory: Arr::string($data, 'homeDirectory', ''),
            environment: Arr::array($data, 'environment'),
        );
    }

    public function toArray(): array
    {
        return [
            'configDir' => $this->configDir,
            'homeDirectory' => $this->homeDirectory,
            'environment' => $this->environment,
        ];
    }
}
