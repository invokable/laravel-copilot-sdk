<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class SandboxProxyCaTrustCertificateRequest implements Arrayable
{
    /**
     * @param  list<string>|null  $allowHosts
     */
    public function __construct(
        public string $certificateFile,
        public string $configDir,
        public ?array $allowHosts = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            certificateFile: Arr::string($data, 'certificateFile', ''),
            configDir: Arr::string($data, 'configDir', ''),
            allowHosts: isset($data['allowHosts']) ? Arr::array($data, 'allowHosts') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'certificateFile' => $this->certificateFile,
            'allowHosts' => $this->allowHosts,
            'configDir' => $this->configDir,
        ], static fn ($value): bool => $value !== null);
    }
}
