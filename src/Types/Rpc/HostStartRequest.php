<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to start a supervised host listener. */
readonly class HostStartRequest implements Arrayable
{
    public function __construct(
        public string $hostId,
        public ?string $computeId = null,
        public HostLocalServerOptions|array|null $localServer = null,
        public HostGitHubEnvironmentOptions|array|null $githubEnvironment = null,
        public ?bool $sessionFactory = null,
        public ?bool $resumeFactory = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            computeId: $data['computeId'] ?? null,
            localServer: isset($data['localServer'])
                ? ($data['localServer'] instanceof HostLocalServerOptions
                    ? $data['localServer']
                    : HostLocalServerOptions::fromArray($data['localServer']))
                : null,
            githubEnvironment: isset($data['githubEnvironment'])
                ? ($data['githubEnvironment'] instanceof HostGitHubEnvironmentOptions
                    ? $data['githubEnvironment']
                    : HostGitHubEnvironmentOptions::fromArray($data['githubEnvironment']))
                : null,
            sessionFactory: $data['sessionFactory'] ?? null,
            resumeFactory: $data['resumeFactory'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hostId' => $this->hostId,
            'computeId' => $this->computeId,
            'localServer' => $this->localServer instanceof HostLocalServerOptions
                ? $this->localServer->toArray()
                : $this->localServer,
            'githubEnvironment' => $this->githubEnvironment instanceof HostGitHubEnvironmentOptions
                ? $this->githubEnvironment->toArray()
                : $this->githubEnvironment,
            'sessionFactory' => $this->sessionFactory,
            'resumeFactory' => $this->resumeFactory,
        ], fn ($value) => $value !== null);
    }
}
