<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Update availability without installation or checkout mutation.
 *
 * @experimental
 */
readonly class SessionPluginsCheckUpdateResult implements Arrayable
{
    public function __construct(
        public bool $updateAvailable,
        public ?string $installedVersion = null,
        public ?string $latestVersion = null,
        public ?bool $notFetched = null,
        public ?string $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            updateAvailable: Arr::boolean($data, 'updateAvailable'),
            installedVersion: isset($data['installedVersion']) ? Arr::string($data, 'installedVersion') : null,
            latestVersion: isset($data['latestVersion']) ? Arr::string($data, 'latestVersion') : null,
            notFetched: isset($data['notFetched']) ? Arr::boolean($data, 'notFetched') : null,
            error: isset($data['error']) ? Arr::string($data, 'error') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'updateAvailable' => $this->updateAvailable,
            'installedVersion' => $this->installedVersion,
            'latestVersion' => $this->latestVersion,
            'notFetched' => $this->notFetched,
            'error' => $this->error,
        ], fn ($value) => $value !== null);
    }
}
